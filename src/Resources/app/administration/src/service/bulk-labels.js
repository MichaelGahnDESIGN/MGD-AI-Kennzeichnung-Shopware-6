export const bulkOptions = {
    status: ['none', 'generated', 'partially-generated', 'modified', 'deepfake'],
    position: ['top-left', 'top-right', 'bottom-left', 'bottom-right'],
    theme: ['auto', 'light', 'dark'],
};

export function selectedImages(items) {
    return [...new Map(items.filter((item) =>
        item.getEntityName?.() === 'media' && item.mediaType?.name === 'IMAGE',
    ).map((item) => [item.id, item])).values()];
}

export function buildBulkPatch(values) {
    const patch = {};
    for (const [field, allowed] of Object.entries(bulkOptions)) {
        const value = values[field];
        if (value === '' || value === undefined) continue;
        if (!allowed.includes(value)) throw new Error('Invalid label value');
        patch[`mgd_ai_${field}`] = value;
    }
    return patch;
}

export async function saveBulkLabels({ items, values, repository, context, canEdit, onProgress = () => {} }) {
    if (!canEdit()) throw new Error('Missing media permission');
    const patch = buildBulkPatch(values);
    if (!Object.keys(patch).length) throw new Error('Empty label change');
    // Auswahl und Sprache vor dem ersten Request festhalten.
    const images = selectedImages(items);
    const requestContext = { ...context };
    const result = { saved: [], failed: [] };
    for (const item of images) {
        try {
            if (!canEdit()) throw new Error('Missing media permission');
            // Frisch laden, damit fremde Felder und lokale Entwürfe erhalten bleiben.
            const entity = await repository.get(item.id, requestContext);
            if (!selectedImages([entity]).length) throw new Error('Image no longer available');
            entity.customFields = { ...(entity.customFields ?? {}), ...patch };
            await repository.save(entity, requestContext);
            result.saved.push(item.id);
        } catch {
            // Keine Serverdetails oder Mediendaten in Meldungen ausgeben.
            result.failed.push(item.id);
        }
        onProgress(result.saved.length + result.failed.length, images.length);
    }
    return result;
}
