#!/usr/bin/env bash

set -euo pipefail

# Ermittelt das Projektverzeichnis unabhängig vom aktuellen Arbeitsordner.
script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)"
project_root="$(cd "${script_dir}/.." && pwd -P)"
composer_file="${project_root}/composer.json"

if [[ ! -f "${composer_file}" ]]; then
    echo "Fehler: composer.json wurde nicht gefunden." >&2
    exit 1
fi

# PHP liest JSON strukturiert; Textsuche oder reguläre Ausdrücke könnten leicht
# die falsche Versionsangabe aus einer Abhängigkeit erwischen.
version="$(php -r '
    $data = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
    if (array_key_exists("version", $data)) {
        fwrite(STDERR, "Fehler: Die Quell-composer.json darf keine Composer-Root-Version enthalten.\n");
        exit(1);
    }
    $version = $data["extra"]["mgd-release-version"] ?? null;
    if (!is_string($version)) {
        fwrite(STDERR, "Fehler: composer.json enthält keine gültige MGD-Release-Version.\n");
        exit(1);
    }
    echo $version;
' "${composer_file}")"

if [[ ! "${version}" =~ ^[0-9]+\.[0-9]+\.[0-9]+([.-][0-9A-Za-z.-]+)?$ ]]; then
    echo "Fehler: Die Plugin-Version besitzt kein zulässiges Release-Format." >&2
    exit 1
fi

temp_parent="$(cd "${TMPDIR:-/tmp}" && pwd -P)"
release_temp_dir="$(mktemp -d "${temp_parent}/mgd-ai-labels-release.XXXXXX")"
validated_temp_dir="$(cd "${release_temp_dir}" && pwd -P)"
validated_dist_directory=""
temporary_archive=""

# Der Trap löscht nur das eine von mktemp erzeugte und kanonisch validierte
# Verzeichnis. Leere, übergeordnete oder nachträglich veränderte Pfade werden
# grundsätzlich nicht an rm übergeben.
cleanup() {
    if [[ -n "${temporary_archive:-}" \
        && -n "${validated_dist_directory:-}" \
        && "${temporary_archive}" == "${validated_dist_directory}/.MGDAIImageLabels-${version}."?????? \
        && -f "${temporary_archive}" \
        && ! -L "${temporary_archive}" ]]; then
        rm -f -- "${temporary_archive}"
    fi

    if [[ -n "${release_temp_dir:-}" \
        && -n "${validated_temp_dir:-}" \
        && "${release_temp_dir}" == "${validated_temp_dir}" \
        && "${release_temp_dir}" == "${temp_parent}/mgd-ai-labels-release."?????? \
        && "${release_temp_dir}" != "${temp_parent}" \
        && -d "${release_temp_dir}" ]]; then
        rm -rf -- "${release_temp_dir}"
    fi
}

# Signale erhalten eigene, eindeutige Exitcodes. Erst das dadurch ausgelöste
# EXIT-Ereignis räumt auf; der Build kann nach einem Abbruch nicht weiterlaufen.
terminate_with_code() {
    local exit_code="$1"
    trap - INT TERM HUP
    exit "${exit_code}"
}

trap cleanup EXIT
trap 'terminate_with_code 130' INT
trap 'terminate_with_code 143' TERM
trap 'terminate_with_code 129' HUP

package_root="${release_temp_dir}/MGDAIImageLabels"
install -d -m 0755 "${package_root}"

# Nur diese ausdrücklich freigegebenen Root-Dateien werden veröffentlicht.
# Entwicklungs-Konfiguration, Testcode und lokale Dateien sind nicht Teil der
# Liste und können daher nicht versehentlich ins Paket gelangen.
readonly root_files=(
    "composer.json"
    "LICENSE"
    "README.md"
    "README.en.md"
    "SECURITY.md"
    "CONTRIBUTING.md"
    "CHANGELOG.md"
)

# Diese beiden Bäume enthalten ausschließlich Plugin-Laufzeitcode und die
# freigegebene menschliche Dokumentation. Symbolische Links sind verboten.
readonly allowed_directories=(
    "src"
    "Dokumentation"
)

for relative_file in "${root_files[@]}"; do
    source_file="${project_root}/${relative_file}"
    if [[ ! -f "${source_file}" || -L "${source_file}" ]]; then
        echo "Fehler: Freigegebene Datei fehlt oder ist ein symbolischer Link: ${relative_file}" >&2
        exit 1
    fi
    install -m 0644 "${source_file}" "${package_root}/${relative_file}"
done

for relative_directory in "${allowed_directories[@]}"; do
    source_directory="${project_root}/${relative_directory}"
    if [[ ! -d "${source_directory}" || -L "${source_directory}" ]]; then
        echo "Fehler: Freigegebenes Verzeichnis fehlt oder ist ein symbolischer Link: ${relative_directory}" >&2
        exit 1
    fi

    if find "${source_directory}" -type l -print -quit | grep -q .; then
        echo "Fehler: Symbolische Links sind in Release-Inhalten nicht zulässig: ${relative_directory}" >&2
        exit 1
    fi

    while IFS= read -r source_file; do
        relative_file="${source_file#"${project_root}/"}"

        # Auch innerhalb erlaubter Bäume bleiben bekannte Geheimnis- und
        # Entwicklungsnamen gesperrt. Zeilenumbrüche verhindern eine sichere
        # sortierte Verarbeitung und werden deshalb ebenfalls abgelehnt.
        if [[ "${relative_file}" == *$'\n'* ]] || [[ "/${relative_file}/" =~ /(\.env([^/]*)?|\.git|tests|vendor|Backups|\.superpowers|node_modules|dist)/ ]]; then
            echo "Fehler: Unzulässiger Release-Pfad: ${relative_file}" >&2
            exit 1
        fi

        target_file="${package_root}/${relative_file}"
        install -d -m 0755 "$(dirname "${target_file}")"
        install -m 0644 "${source_file}" "${target_file}"
    done < <(find "${source_directory}" -type f -print | LC_ALL=C sort)
done

# Composer empfiehlt für das Quell-Repository keine feste Root-Version, während
# Shopware sie im installierbaren Plugin-Paket verlangt. Erst die isolierte
# Paketkopie erhält deshalb die zuvor validierte Release-Version.
php -r '
    $path = $argv[1];
    $releaseVersion = $argv[2];
    $composer = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    $composer["version"] = $releaseVersion;
    $encoded = json_encode(
        $composer,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    );
    file_put_contents($path, $encoded . "\n", LOCK_EX);
' "${package_root}/composer.json" "${version}"

dist_directory="${project_root}/dist"
if [[ -L "${dist_directory}" ]]; then
    echo "Fehler: dist darf kein symbolischer Link sein." >&2
    exit 1
fi
if [[ -e "${dist_directory}" ]]; then
    if [[ ! -d "${dist_directory}" ]]; then
        echo "Fehler: dist existiert, ist aber kein echtes Verzeichnis." >&2
        exit 1
    fi
else
    install -d -m 0755 "${dist_directory}"
fi

# Die kanonische Prüfung schützt auch vor unerwarteten umgeleiteten Pfaden.
# Sie muss exakt das dist-Verzeichnis unter dem bereits kanonischen Projekt sein.
validated_dist_directory="$(cd "${dist_directory}" && pwd -P)"
if [[ "${validated_dist_directory}" != "${project_root}/dist" ]]; then
    echo "Fehler: dist zeigt nicht auf das erwartete Projektverzeichnis." >&2
    exit 1
fi

archive_path="${validated_dist_directory}/MGDAIImageLabels-${version}.zip"
if [[ -L "${archive_path}" ]] || [[ -e "${archive_path}" && ! -f "${archive_path}" ]]; then
    echo "Fehler: Das Release-Ziel ist kein reguläres, plugin-eigenes Paket." >&2
    exit 1
fi

# Die temporäre ZIP liegt absichtlich im echten Zielverzeichnis. Damit findet
# der abschließende atomare Austausch garantiert innerhalb desselben
# Dateisystems statt, selbst wenn der Arbeitsbereich in einem System-Temp liegt.
temporary_archive="$(mktemp "${validated_dist_directory}/.MGDAIImageLabels-${version}.XXXXXX")"
if [[ ! -f "${temporary_archive}" || -L "${temporary_archive}" \
    || "$(cd "$(dirname "${temporary_archive}")" && pwd -P)" != "${validated_dist_directory}" ]]; then
    echo "Fehler: Die temporäre Release-Datei konnte nicht sicher validiert werden." >&2
    exit 1
fi

# Python erzeugt Einträge in stabiler Reihenfolge, mit festem Zeitstempel und
# festen Unix-Rechten. Dadurch sind zwei Builds desselben Quellstands bytegleich.
python3 - "${package_root}" "${temporary_archive}" <<'PYTHON'
from __future__ import annotations

import pathlib
import stat
import sys
import zipfile

package_root = pathlib.Path(sys.argv[1])
archive_path = pathlib.Path(sys.argv[2])
fixed_time = (1980, 1, 1, 0, 0, 0)

files = sorted(path for path in package_root.rglob("*") if path.is_file())
if not files:
    raise SystemExit("Fehler: Das Release enthält keine Dateien.")

with zipfile.ZipFile(
    archive_path,
    mode="w",
    compression=zipfile.ZIP_DEFLATED,
    compresslevel=9,
) as archive:
    directories = {pathlib.PurePosixPath("MGDAIImageLabels")}
    for source in files:
        relative = pathlib.PurePosixPath(source.relative_to(package_root).as_posix())
        for parent in relative.parents:
            if str(parent) != ".":
                directories.add(pathlib.PurePosixPath("MGDAIImageLabels") / parent)

    for directory in sorted(directories, key=lambda item: item.as_posix()):
        info = zipfile.ZipInfo(directory.as_posix().rstrip("/") + "/", fixed_time)
        info.create_system = 3
        info.external_attr = (stat.S_IFDIR | 0o755) << 16
        info.compress_type = zipfile.ZIP_STORED
        archive.writestr(info, b"")

    for source in files:
        relative = source.relative_to(package_root).as_posix()
        info = zipfile.ZipInfo(f"MGDAIImageLabels/{relative}", fixed_time)
        info.create_system = 3
        info.external_attr = (stat.S_IFREG | 0o644) << 16
        info.compress_type = zipfile.ZIP_DEFLATED
        archive.writestr(info, source.read_bytes(), compress_type=zipfile.ZIP_DEFLATED, compresslevel=9)
PYTHON

# os.replace tauscht ausschließlich das exakt benannte Zielpaket atomar aus.
python3 - "${temporary_archive}" "${archive_path}" <<'PYTHON'
import os
import sys

os.replace(sys.argv[1], sys.argv[2])
PYTHON

checksum="$(shasum -a 256 "${archive_path}" | awk '{print $1}')"
echo "Release erstellt: ${archive_path}"
echo "SHA-256: ${checksum}"
