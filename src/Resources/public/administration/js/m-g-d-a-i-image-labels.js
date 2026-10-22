(()=>{var C=`<sw-media-collapse v-if="images.length || busy" :title="$t('mgd-ai-image-labels.bulk.title')" :expand-on-loading="true">
    <template #content>
        <div class="mgd-ai-media-bulk">
            <p>{{ $t('mgd-ai-image-labels.bulk.selection', { count: images.length }) }}</p>
            <p>{{ $t('mgd-ai-image-labels.bulk.help') }}</p>
            <fieldset :disabled="busy || !allowed">
                <legend class="mgd-ai-media-bulk__legend">{{ $t('mgd-ai-image-labels.bulk.title') }}</legend>
                <label v-for="field in fields" :key="field">
                    <span>{{ $t('mgd-ai-image-labels.bulk.' + field) }}</span>
                    <select v-model="values[field]">
                        <option value="">{{ $t('mgd-ai-image-labels.bulk.unchanged') }}</option>
                        <option v-for="value in options[field]" :key="value" :value="value">{{ label(field, value) }}</option>
                    </select>
                </label>
                <button type="button" :disabled="busy || !allowed || !changed || !images.length" @click="apply">
                    {{ $t('mgd-ai-image-labels.bulk.apply', { count: images.length }) }}
                </button>
            </fieldset>
            <p v-if="!allowed">{{ $t('mgd-ai-image-labels.bulk.permission') }}</p>
            <p role="status" aria-live="polite">{{ progress || outcome }}</p>
        </div>
    </template>
</sw-media-collapse>
`;var d={status:["none","generated","partially-generated","modified","deepfake"],position:["top-left","top-right","bottom-left","bottom-right"],theme:["auto","light","dark"]};function m(e){return[...new Map(e.filter(t=>t.getEntityName?.()==="media"&&t.mediaType?.name==="IMAGE").map(t=>[t.id,t])).values()]}function f(e){let t={};for(let[i,o]of Object.entries(d)){let a=e[i];if(!(a===""||a===void 0)){if(!o.includes(a))throw new Error("Invalid label value");t[`mgd_ai_${i}`]=a}}return t}async function P({items:e,values:t,repository:i,context:o,canEdit:a,onProgress:Z=()=>{}}){if(!a())throw new Error("Missing media permission");let S=f(t);if(!Object.keys(S).length)throw new Error("Empty label change");let _=m(e),E={...o},l={saved:[],failed:[]};for(let b of _){try{if(!a())throw new Error("Missing media permission");let s=await i.get(b.id,E);if(!m([s]).length)throw new Error("Image no longer available");s.customFields={...s.customFields??{},...S},await i.save(s,E),l.saved.push(b.id)}catch{l.failed.push(b.id)}Z(l.saved.length+l.failed.length,_.length)}return l}Shopware.Component.register("mgd-ai-media-bulk",{template:C,inject:["repositoryFactory","acl"],props:{items:{type:Array,required:!0}},data(){return{values:{status:"",position:"",theme:""},busy:!1,progress:"",outcome:""}},computed:{images(){return m(this.items)},allowed(){return this.acl.can("media.editor")},changed(){return Object.keys(f(this.values)).length>0},fields(){return Object.keys(d)},options(){return d},selectionKey(){return this.images.map(e=>e.id).sort().join(",")}},watch:{selectionKey(){this.busy||(this.outcome="",this.values={status:"",position:"",theme:""})}},methods:{label(e,t){let i=t.replace(/-([a-z])/g,(o,a)=>a.toUpperCase());return this.$t(`mgd-ai-image-labels.preview.${e}.${i}`)},async apply(){if(!(this.busy||!this.allowed||!this.changed||!this.images.length)){this.busy=!0,this.outcome="",this.progress="";try{let e=await P({items:this.items,values:this.values,repository:this.repositoryFactory.create("media"),context:Shopware.Context.api,canEdit:()=>this.acl.can("media.editor"),onProgress:(t,i)=>{this.progress=`${t} / ${i}`}});this.outcome=this.$t("mgd-ai-image-labels.bulk.result",{saved:e.saved.length,failed:e.failed.length}),this.values={status:"",position:"",theme:""}}catch{this.outcome=this.$t("mgd-ai-image-labels.bulk.error")}finally{this.busy=!1,this.progress=""}}}}});var I=`{% block sw_media_quickinfo_multiple_file_names %}
    <mgd-ai-media-bulk v-if="editable" :items="items" />
    {% parent %}
{% endblock %}
`;Shopware.Component.override("sw-media-quickinfo-multiple",{template:I});var T={"mgd-ai-image-labels":{preview:{title:"Vorschau der KI-Bildkennzeichnung",helpText:"Die Vorschau wird nur lokal aktualisiert. Speichern Sie \xC4nderungen mit der vorhandenen Shopware-Speicheraktion der Custom Fields.",imageAlt:"Lokale Vorschau des ausgew\xE4hlten Bildmediums",missingImage:"F\xFCr dieses Bild ist noch keine lokale Vorschau verf\xFCgbar.",announcement:"Vorschau: Status {status}, Position {position}, Darstellung {theme}.",status:{none:"KEINE SICHTBARE KENNZEICHNUNG",generated:"KI-GENERIERT",partiallyGenerated:"TEILWEISE KI-GENERIERT",modified:"MIT KI BEARBEITET",deepfake:"KI-DEEPFAKE"},position:{topLeft:"oben links",topRight:"oben rechts",bottomLeft:"unten links",bottomRight:"unten rechts"},theme:{auto:"automatisch",light:"hell",dark:"dunkel"}},philosophy:{label:"Unsere Haltung zu KI-Bildern",preview:"Transparente Informationen zur Erstellung und Bearbeitung von Bildern.",contentLabel:"Inhalt",defaultContent:"<h2>Unser Umgang mit KI-Bildern</h2><p>Wir kennzeichnen Bilder transparent, wenn sie vollst\xE4ndig oder teilweise mit k\xFCnstlicher Intelligenz erstellt oder bearbeitet wurden.</p><p>So k\xF6nnen Sie Inhalte bewusst einordnen. Die Kennzeichnung ver\xE4ndert weder Produktinformationen noch Ihre Privatsph\xE4re.</p>"},settings:{title:"MGD KI-Bildkennzeichnung",description:"Erstellen Sie einmalig ein unverkn\xFCpftes Erlebniswelten-Layout mit Ihrer zweisprachigen AI-Philosophie.",cardTitle:"AI-Philosophie als Erlebniswelt",noPublishNotice:"Die Aktion ver\xF6ffentlicht oder verkn\xFCpft nichts. Pr\xFCfen, bearbeiten und ver\xF6ffentlichen Sie das Layout anschlie\xDFend bewusst in den Erlebniswelten.",action:"Erlebniswelt vorbereiten",missingPermission:"F\xFCr diese Aktion fehlen Rechte f\xFCr Systemkonfiguration, CMS-Seiten, Sektionen, Bl\xF6cke oder Elemente.",successCreated:"Die Erlebniswelt wurde sicher vorbereitet.",successExisting:"Die bereits vorhandene plugin-eigene Erlebniswelt wurde gefunden.",error:"Die Erlebniswelt konnte nicht vorbereitet werden. Bitte pr\xFCfen Sie Berechtigung und Systemprotokoll.",openEditor:"In Erlebniswelten \xF6ffnen"},cms:{background:{label:"Gekennzeichnetes Hintergrundbild",preview:"Lokales Hintergrundbild mit KI-Kennzeichnung",contentPlaceholder:"Inhalte des Erlebniswelten-Blocks",fields:{media:"Bildmedium",minHeight:"Mindesth\xF6he",horizontalPosition:"Horizontale Bildposition",verticalPosition:"Vertikale Bildposition",decorative:"Bild ist rein dekorativ",fallbackColor:"Sichere Hintergrundfarbe",altText:"Alternativtext",altTextHelp:"Beschreiben Sie knapp den Inhalt oder Zweck des Bildes. Lassen Sie das Feld nur bei ausdr\xFCcklich dekorativen Bildern leer."},validation:{imageOnly:"Bitte w\xE4hlen Sie ausschlie\xDFlich ein Bildmedium. Dokumente, Videos und andere Dateien sind nicht zul\xE4ssig.",missingAltText:"F\xFCr ein nicht dekoratives Bild fehlt ein Alternativtext. Ohne Beschreibung k\xF6nnen blinde Nutzer den Bildinhalt nicht erfassen."},options:{minHeight:{"240":"Kompakt (240 px)","320":"Standard (320 px)","480":"Gro\xDF (480 px)","640":"Sehr gro\xDF (640 px)"},horizontalPosition:{left:"Links",center:"Mitte",right:"Rechts"},verticalPosition:{top:"Oben",center:"Mitte",bottom:"Unten"},fallbackColor:{"neutral-light":"Neutral hell","neutral-dark":"Neutral dunkel",brand:"Blau"}}}},bulk:{title:"KI-Kennzeichnung",selection:"{count} Bilder ausgew\xE4hlt",help:"Nur ausgew\xE4hlte Bilder werden ge\xE4ndert. Ordner und andere Dateien bleiben unver\xE4ndert. Die \xC4nderungen gelten f\xFCr die oben gew\xE4hlte Sprache.",status:"Kennzeichnung",position:"Position",theme:"Darstellung",unchanged:"Unver\xE4ndert lassen",apply:"Auf {count} Bilder anwenden",permission:"Zum \xC4ndern ist das Recht \u201EMedien bearbeiten\u201C erforderlich.",result:"{saved} Bilder gespeichert, {failed} fehlgeschlagen. Bei Fehlern die betroffenen Bilder pr\xFCfen und erneut ausw\xE4hlen.",error:"Die \xC4nderungen konnten nicht gespeichert werden. Bitte Berechtigungen und Verbindung pr\xFCfen."}}};var L={"mgd-ai-image-labels":{preview:{title:"AI image label preview",helpText:"The preview updates locally only. Save changes with the existing Shopware custom-field save action.",imageAlt:"Local preview of the selected image medium",missingImage:"No local preview is available for this image yet.",announcement:"Preview: status {status}, position {position}, appearance {theme}.",status:{none:"NO VISIBLE LABEL",generated:"AI GENERATED",partiallyGenerated:"AI PARTIALLY GENERATED",modified:"AI MODIFIED",deepfake:"AI DEEPFAKE"},position:{topLeft:"top left",topRight:"top right",bottomLeft:"bottom left",bottomRight:"bottom right"},theme:{auto:"automatic",light:"light",dark:"dark"}},philosophy:{label:"Our approach to AI imagery",preview:"Transparent information about how images are created and edited.",contentLabel:"Content",defaultContent:"<h2>Our approach to AI imagery</h2><p>We label images transparently when they were created or edited wholly or partly with artificial intelligence.</p><p>This helps you assess content consciously. The label changes neither product information nor your privacy.</p>"},settings:{title:"MGD AI Image Labels",description:"Create an unassigned Shopping Experiences layout containing your bilingual AI philosophy once.",cardTitle:"AI philosophy Shopping Experience",noPublishNotice:"This action publishes or assigns nothing. Review, edit and publish the layout consciously in Shopping Experiences afterwards.",action:"Prepare Shopping Experience",missingPermission:"This action requires permissions for system configuration, CMS pages, sections, blocks and elements.",successCreated:"The Shopping Experience was prepared safely.",successExisting:"The existing plugin-owned Shopping Experience was found.",error:"The Shopping Experience could not be prepared. Please check your permission and the system log.",openEditor:"Open in Shopping Experiences"},cms:{background:{label:"Labeled background image",preview:"Local background image with AI label",contentPlaceholder:"Shopping Experiences block content",fields:{media:"Image medium",minHeight:"Minimum height",horizontalPosition:"Horizontal image position",verticalPosition:"Vertical image position",decorative:"Image is purely decorative",fallbackColor:"Safe background colour",altText:"Alternative text",altTextHelp:"Briefly describe the image content or purpose. Leave this field empty only for explicitly decorative images."},validation:{imageOnly:"Please select an image medium only. Documents, videos and other files are not permitted.",missingAltText:"A non-decorative image is missing alternative text. Without a description, blind users cannot understand the image content."},options:{minHeight:{"240":"Compact (240 px)","320":"Standard (320 px)","480":"Large (480 px)","640":"Extra large (640 px)"},horizontalPosition:{left:"Left",center:"Centre",right:"Right"},verticalPosition:{top:"Top",center:"Centre",bottom:"Bottom"},fallbackColor:{"neutral-light":"Neutral light","neutral-dark":"Neutral dark",brand:"Blue"}}}},bulk:{title:"AI labels",selection:"{count} images selected",help:"Only selected images are changed. Folders and other files remain unchanged. Changes apply to the language selected above.",status:"Label",position:"Position",theme:"Appearance",unchanged:"Leave unchanged",apply:"Apply to {count} images",permission:"The media editing permission is required.",result:"{saved} images saved, {failed} failed. If errors occur, check the affected images and select them again.",error:"Changes could not be saved. Please check permissions and connection."}}};var z=`{% block mgd_ai_media_preview %}
<section
    class="mgd-ai-media-preview"
    :aria-label="$t('mgd-ai-image-labels.preview.title')"
>
    <h3 class="mgd-ai-media-preview__title">
        {{ $t('mgd-ai-image-labels.preview.title') }}
    </h3>

    <p class="mgd-ai-media-preview__help">
        {{ $t('mgd-ai-image-labels.preview.helpText') }}
    </p>

    <div
        v-if="item.url"
        class="mgd-ai-media-preview__canvas"
    >
        <img
            class="mgd-ai-media-preview__image"
            :src="item.url"
            :alt="$t('mgd-ai-image-labels.preview.imageAlt')"
        >

        <span
            v-if="previewState.visible"
            class="mgd-ai-media-preview__badge"
            :class="[
                previewPresentation.positionClass,
                previewPresentation.themeClass,
            ]"
            role="note"
        >
            {{ $t(previewPresentation.labelSnippet) }}
        </span>
    </div>

    <p
        v-else
        class="mgd-ai-media-preview__missing-image"
    >
        {{ $t('mgd-ai-image-labels.preview.missingImage') }}
    </p>

    <p
        class="mgd-ai-media-preview__status"
        aria-live="polite"
        aria-atomic="true"
    >
        {{ $t('mgd-ai-image-labels.preview.announcement', {
            status: $t(previewPresentation.labelSnippet),
            position: $t(previewPresentation.positionSnippet),
            theme: $t(previewPresentation.themeSnippet),
        }) }}
    </p>
</section>
{% endblock %}
`;var ie=Object.freeze(["none","generated","partially-generated","modified","deepfake"]),ae=Object.freeze(["top-left","top-right","bottom-left","bottom-right"]),oe=Object.freeze(["auto","light","dark"]),ne=Object.freeze({"top-left":"is--top-left","top-right":"is--top-right","bottom-left":"is--bottom-left","bottom-right":"is--bottom-right"}),re=Object.freeze({auto:"is--auto",light:"is--light",dark:"is--dark"}),le=Object.freeze({none:"mgd-ai-image-labels.preview.status.none",generated:"mgd-ai-image-labels.preview.status.generated","partially-generated":"mgd-ai-image-labels.preview.status.partiallyGenerated",modified:"mgd-ai-image-labels.preview.status.modified",deepfake:"mgd-ai-image-labels.preview.status.deepfake"}),se=Object.freeze({"top-left":"mgd-ai-image-labels.preview.position.topLeft","top-right":"mgd-ai-image-labels.preview.position.topRight","bottom-left":"mgd-ai-image-labels.preview.position.bottomLeft","bottom-right":"mgd-ai-image-labels.preview.position.bottomRight"}),me=Object.freeze({auto:"mgd-ai-image-labels.preview.theme.auto",light:"mgd-ai-image-labels.preview.theme.light",dark:"mgd-ai-image-labels.preview.theme.dark"});function v(e={}){let t=typeof e=="object"&&e!==null?e:{},i=ie.includes(t.status)?t.status:"none",o=ae.includes(t.position)?t.position:"bottom-right",a=oe.includes(t.theme)?t.theme:"auto";return{status:i,position:o,theme:a,visible:i!=="none"}}function A(e={}){let t=v(e);return{positionClass:ne[t.position],themeClass:re[t.theme],labelSnippet:le[t.status],positionSnippet:se[t.position],themeSnippet:me[t.theme]}}Shopware.Component.register("mgd-ai-media-preview",{template:z,props:{item:{type:Object,required:!0}},computed:{previewInput(){let e=this.item.customFields??{};return{status:e.mgd_ai_status,position:e.mgd_ai_position,theme:e.mgd_ai_theme}},previewState(){return v(this.previewInput)},previewPresentation(){return A(this.previewState)}}});var B=`{% block sw_media_quickinfo_custom_field_sets %}
    {% parent %}

    <mgd-ai-media-preview
        v-if="item.mediaType && item.mediaType.name === 'IMAGE'"
        :item="item"
    />
{% endblock %}
`;Shopware.Component.override("sw-media-quickinfo",{template:B});var O=`<div class="mgd-ai-background-image" :class="presentationClasses">
    <img v-if="mediaSource" class="mgd-ai-background-image__media" :src="mediaSource" alt="">
    <div class="mgd-ai-background-image__content">
        {{ $tc('mgd-ai-image-labels.cms.background.contentPlaceholder') }}
    </div>
</div>
`;var n=Object.freeze({minHeight:Object.freeze(["240px","320px","480px","640px"]),horizontalPosition:Object.freeze(["left","center","right"]),verticalPosition:Object.freeze(["top","center","bottom"]),fallbackColor:Object.freeze(["neutral-light","neutral-dark","brand"])}),c=Object.freeze({minHeight:"320px",horizontalPosition:"center",verticalPosition:"center",fallbackColor:"neutral-light",decorative:!1,altText:""});function w(e){return e===!0}function p(e){return typeof e!="string"?"":[...e.replace(/<(script|style)\b[^>]*>[\s\S]*?<\/\1\s*>/giu,"").replace(/<[^>]*>/gu,"").replace(/[\u0000-\u001F\u007F]+/gu," ").trim()].slice(0,512).join("")}function $(e,t){let i=typeof t?.translated=="object"&&t.translated!==null?t.translated:{};for(let o of[e,i.alt,i.title]){let a=p(o);if(a!=="")return a}return""}function k(e){if(typeof e!="object"||e===null||typeof e.mimeType!="string"||!e.mimeType.startsWith("image/"))return!1;let t=e.mediaType;return typeof t=="object"&&t!==null&&typeof t.name=="string"&&t.name==="IMAGE"}function g(e,t,i){return typeof e=="string"&&t.includes(e)?e:i}function r(e={}){let t=typeof e=="object"&&e!==null?e:{};return{minHeight:g(t.minHeight,n.minHeight,c.minHeight),horizontalPosition:g(t.horizontalPosition,n.horizontalPosition,c.horizontalPosition),verticalPosition:g(t.verticalPosition,n.verticalPosition,c.verticalPosition),fallbackColor:g(t.fallbackColor,n.fallbackColor,c.fallbackColor),decorative:w(t.decorative),altText:p(t.altText)}}var{Mixin:ge}=Shopware,N={template:O,mixins:[ge.getByName("cms-element")],computed:{presentation(){return r({minHeight:this.element?.config?.minHeight?.value,horizontalPosition:this.element?.config?.horizontalPosition?.value,verticalPosition:this.element?.config?.verticalPosition?.value,fallbackColor:this.element?.config?.fallbackColor?.value,decorative:this.element?.config?.decorative?.value,altText:this.element?.config?.altText?.value})},presentationClasses(){return[`mgd-ai-background-image--height-${this.presentation.minHeight.replace("px","")}`,`mgd-ai-background-image--horizontal-${this.presentation.horizontalPosition}`,`mgd-ai-background-image--vertical-${this.presentation.verticalPosition}`,`mgd-ai-background-image--fallback-${this.presentation.fallbackColor}`]},mediaSource(){return this.element?.data?.media?.url??null}},created(){this.initElementConfig("mgd-ai-background-image"),this.initElementData("mgd-ai-background-image")}};var D=`<div class="sw-cms-el-config-mgd-ai-background-image">
    <sw-media-upload-v2
        variant="regular"
        :upload-tag="uploadTag"
        :source="previewSource"
        :allow-multi-select="false"
        file-accept="image/*"
        :caption="$tc('mgd-ai-image-labels.cms.background.fields.media')"
        @media-upload-sidebar-open="mediaModalIsOpen = true"
        @media-upload-remove-image="onImageRemove"
    />
    <sw-upload-listener :upload-tag="uploadTag" auto-upload @media-upload-finish="onImageUpload" />
    <sw-media-modal-v2
        v-if="mediaModalIsOpen"
        :allow-multi-select="false"
        file-accept="image/*"
        @media-modal-selection-change="onSelectionChanges"
        @modal-close="mediaModalIsOpen = false"
    />

    <mt-banner v-if="selectionError" variant="error">
        {{ $tc('mgd-ai-image-labels.cms.background.validation.imageOnly') }}
    </mt-banner>

    <mt-select v-model:model-value="element.config.minHeight.value" :label="$tc('mgd-ai-image-labels.cms.background.fields.minHeight')" :options="minHeightOptions" @update:model-value="emitUpdate" />
    <mt-select v-model:model-value="element.config.horizontalPosition.value" :label="$tc('mgd-ai-image-labels.cms.background.fields.horizontalPosition')" :options="horizontalOptions" @update:model-value="emitUpdate" />
    <mt-select v-model:model-value="element.config.verticalPosition.value" :label="$tc('mgd-ai-image-labels.cms.background.fields.verticalPosition')" :options="verticalOptions" @update:model-value="emitUpdate" />
    <mt-select v-model:model-value="element.config.fallbackColor.value" :label="$tc('mgd-ai-image-labels.cms.background.fields.fallbackColor')" :options="fallbackOptions" @update:model-value="emitUpdate" />
    <mt-switch :checked="element.config.decorative.value" :label="$tc('mgd-ai-image-labels.cms.background.fields.decorative')" @change="onChangeDecorative" />
    <mt-text-field
        v-model:model-value="element.config.altText.value"
        :label="$tc('mgd-ai-image-labels.cms.background.fields.altText')"
        :help-text="$tc('mgd-ai-image-labels.cms.background.fields.altTextHelp')"
        :disabled="element.config.decorative.value === true"
        @update:model-value="onChangeAltText"
    />
    <mt-banner v-if="missingAltText" variant="warning">
        {{ $tc('mgd-ai-image-labels.cms.background.validation.missingAltText') }}
    </mt-banner>
</div>
`;var{Mixin:ue}=Shopware,j={template:D,compatConfig:Shopware.compatConfig,inject:["repositoryFactory"],emits:["element-update"],mixins:[ue.getByName("cms-element")],data(){return{mediaModalIsOpen:!1,selectionError:!1}},computed:{mediaRepository(){return this.repositoryFactory.create("media")},uploadTag(){return`mgd-ai-background-image-${this.element.id}`},previewSource(){return this.element?.data?.media??this.element?.config?.media?.value??null},minHeightOptions(){return this.options("minHeight",n.minHeight)},horizontalOptions(){return this.options("horizontalPosition",n.horizontalPosition)},verticalOptions(){return this.options("verticalPosition",n.verticalPosition)},fallbackOptions(){return this.options("fallbackColor",n.fallbackColor)},missingAltText(){return this.element?.config?.decorative?.value!==!0&&$(this.element?.config?.altText?.value,this.element?.data?.media)===""}},created(){this.initElementConfig("mgd-ai-background-image"),this.normalizeElementConfig()},methods:{options(e,t){return t.map(i=>({value:i,label:this.$tc(`mgd-ai-image-labels.cms.background.options.${e}.${i.replace("px","")}`)}))},normalizeElementConfig(){let e=r({minHeight:this.element.config.minHeight.value,horizontalPosition:this.element.config.horizontalPosition.value,verticalPosition:this.element.config.verticalPosition.value,fallbackColor:this.element.config.fallbackColor.value,decorative:this.element.config.decorative.value,altText:this.element.config.altText.value});for(let[t,i]of Object.entries(e))this.element.config[t].value=i},emitUpdate(){this.normalizeElementConfig(),this.$emit("element-update",this.element)},async onImageUpload({targetId:e}){let t=await this.mediaRepository.get(e,Shopware.Context.api);if(!k(t)){this.rejectMedia();return}this.setMedia(t)},onSelectionChanges(e){let t=Array.isArray(e)?e[0]:null;if(!t?.id||!k(t)){this.rejectMedia();return}this.setMedia(t)},setMedia(e){this.element.config.media.value=e.id,this.element.config.media.source="static",this.updateElementData(e),this.selectionError=!1,this.mediaModalIsOpen=!1,this.emitUpdate()},onImageRemove(){this.element.config.media.value=null,this.updateElementData(),this.selectionError=!1,this.emitUpdate()},rejectMedia(){this.selectionError=!0},onChangeDecorative(e){this.element.config.decorative.value=w(e),this.emitUpdate()},onChangeAltText(e){this.element.config.altText.value=p(e),this.emitUpdate()},updateElementData(e=null){let t=e===null?null:e.id;if(!this.element.data){this.isCompatEnabled("INSTANCE_SET")?this.$set(this.element,"data",{mediaId:t,media:e}):this.element.data={mediaId:t,media:e};return}this.isCompatEnabled("INSTANCE_SET")?(this.$set(this.element.data,"mediaId",t),this.$set(this.element.data,"media",e)):(this.element.data.mediaId=t,this.element.data.media=e)}}};var H=`<div class="mgd-ai-background-image-preview" :class="previewClass">
    <span>{{ $tc('mgd-ai-image-labels.cms.background.preview') }}</span>
</div>
`;var M={template:H,computed:{previewClass(){return`mgd-ai-background-image--height-${r().minHeight.replace("px","")}`}}};Shopware.Component.register("sw-cms-el-mgd-ai-background-image",N);Shopware.Component.register("sw-cms-el-config-mgd-ai-background-image",j);Shopware.Component.register("sw-cms-el-preview-mgd-ai-background-image",M);Shopware.Service("cmsService").registerCmsElement({name:"mgd-ai-background-image",label:"mgd-ai-image-labels.cms.background.label",component:"sw-cms-el-mgd-ai-background-image",configComponent:"sw-cms-el-config-mgd-ai-background-image",previewComponent:"sw-cms-el-preview-mgd-ai-background-image",defaultConfig:{media:{source:"static",value:null,required:!0},minHeight:{source:"static",value:"320px"},horizontalPosition:{source:"static",value:"center"},verticalPosition:{source:"static",value:"center"},decorative:{source:"static",value:!1},fallbackColor:{source:"static",value:"neutral-light"},altText:{source:"static",value:""}}});var R=`{% block sw_cms_element_mgd_ai_philosophy %}
<section class="mgd-ai-philosophy-admin">
    <h3>{{ $tc('mgd-ai-image-labels.philosophy.label') }}</h3>
    <p>{{ plainContent || $tc('mgd-ai-image-labels.philosophy.preview') }}</p>
</section>
{% endblock %}
`;var U=Object.freeze({"de-DE":"<h2>Unser Umgang mit KI-Bildern</h2><p>Wir kennzeichnen Bilder transparent, wenn sie vollst\xE4ndig oder teilweise mit k\xFCnstlicher Intelligenz erstellt oder bearbeitet wurden.</p><p>So k\xF6nnen Sie Inhalte bewusst einordnen. Die Kennzeichnung ver\xE4ndert weder Produktinformationen noch Ihre Privatsph\xE4re.</p>","en-GB":"<h2>Our approach to AI imagery</h2><p>We label images transparently when they were created or edited wholly or partly with artificial intelligence.</p><p>This helps you assess content consciously. The label changes neither product information nor your privacy.</p>"});function y(e){try{let t=e()?.api?.languageId;return typeof t=="string"&&t.trim()!==""?t.trim():null}catch{return null}}function fe(e){try{let t=e?.Store,i=t?.get;if(typeof i!="function")return null;let o=t.list;if(typeof o=="function"){let a=o.call(t);if(!Array.isArray(a)||!a.includes("context"))return null}return y(()=>i.call(t,"context"))}catch{return null}}function ve(e=globalThis.Shopware){return fe(e)??y(()=>e?.State?.get?.("context"))??y(()=>e?.Context)??null}function we(e){return typeof e?.locale?.code=="string"&&e.locale.code.trim()!==""?e.locale.code:"en-GB"}function ke(e){let t=0;return{async load(i){let o=++t,a=null;try{a=typeof i=="string"&&i!==""?await e(i):null}catch{a=null}return o===t?we(a):null},cancel(){++t}}}var u={inject:["repositoryFactory"],data(){return{contentLocale:"en-GB",contentLanguageLocaleLoader:null}},computed:{contentLanguageId(){return ve(Shopware)},languageRepository(){return this.repositoryFactory.create("language")}},watch:{contentLanguageId(e){this.refreshContentLocale(e)}},created(){this.contentLanguageLocaleLoader=ke(async e=>{let t=new Shopware.Data.Criteria(1,1);return t.addAssociation("locale"),this.languageRepository.get(e,Shopware.Context.api,t)}),this.refreshContentLocale(this.contentLanguageId)},beforeDestroy(){this.contentLanguageLocaleLoader?.cancel()},beforeUnmount(){this.contentLanguageLocaleLoader?.cancel()},methods:{async refreshContentLocale(e){let t=await this.contentLanguageLocaleLoader.load(e);t!==null&&(this.contentLocale=t)}}};function h(e,t){return typeof e=="string"&&e.trim()!==""?e:typeof t=="string"&&/^de(?:[-_]|$)/i.test(t.trim())?U["de-DE"]:U["en-GB"]}var{Mixin:ye}=Shopware,F={template:R,mixins:[ye.getByName("cms-element"),u],computed:{plainContent(){return h(this.element?.config?.content?.value,this.contentLocale).replace(/<[^>]*>/g," ").replace(/\s+/g," ").trim()}},created(){this.initElementConfig("mgd-ai-philosophy")}};var K=`{% block sw_cms_element_config_mgd_ai_philosophy %}
<sw-cms-mapping-field
    v-model:config="element.config.content"
    :label="$tc('mgd-ai-image-labels.philosophy.contentLabel')"
    value-types="string"
>
    <sw-text-editor
        :value="resolvedContent"
        :allow-inline-data-mapping="true"
        :sanitize-info-warn="true"
        sanitize-input
        enable-transparent-background
        @update:value="onInput"
    />
</sw-cms-mapping-field>
{% endblock %}
`;var{Mixin:Se}=Shopware,G={template:K,compatConfig:Shopware.compatConfig,emits:["element-update"],mixins:[Se.getByName("cms-element"),u],computed:{resolvedContent(){return h(this.element?.config?.content?.value,this.contentLocale)}},created(){this.initElementConfig("mgd-ai-philosophy")},methods:{onInput(e){this.element.config.content.value=typeof e=="string"?e:"",this.$emit("element-update",this.element)}}};var q=`{% block sw_cms_element_preview_mgd_ai_philosophy %}
<div class="sw-cms-el-preview-mgd-ai-philosophy">
    <strong>{{ $tc('mgd-ai-image-labels.philosophy.label') }}</strong>
    <p>{{ $tc('mgd-ai-image-labels.philosophy.preview') }}</p>
</div>
{% endblock %}
`;var V={template:q};Shopware.Component.register("sw-cms-el-mgd-ai-philosophy",F);Shopware.Component.register("sw-cms-el-config-mgd-ai-philosophy",G);Shopware.Component.register("sw-cms-el-preview-mgd-ai-philosophy",V);Shopware.Service("cmsService").registerCmsElement({name:"mgd-ai-philosophy",label:"mgd-ai-image-labels.philosophy.label",component:"sw-cms-el-mgd-ai-philosophy",configComponent:"sw-cms-el-config-mgd-ai-philosophy",previewComponent:"sw-cms-el-preview-mgd-ai-philosophy",defaultConfig:{content:{source:"static",value:null}}});var W=`{% block mgd_ai_settings_index %}
<sw-page class="mgd-ai-settings-index">
    <template #smart-bar-header>
        <h2>{{ $tc('mgd-ai-image-labels.settings.title') }}</h2>
    </template>

    <template #content>
        <sw-card-view>
            <sw-card :title="$tc('mgd-ai-image-labels.settings.cardTitle')">
                <p>{{ $tc('mgd-ai-image-labels.settings.description') }}</p>
                <sw-alert variant="info">{{ $tc('mgd-ai-image-labels.settings.noPublishNotice') }}</sw-alert>
                <sw-button-process
                    v-if="canPrepare"
                    variant="primary"
                    :is-loading="isLoading"
                    :disabled="isLoading"
                    @click="preparePage"
                >
                    {{ $tc('mgd-ai-image-labels.settings.action') }}
                </sw-button-process>
                <sw-alert v-else variant="warning">{{ $tc('mgd-ai-image-labels.settings.missingPermission') }}</sw-alert>
                <router-link v-if="cmsPageRoute" :to="cmsPageRoute">
                    {{ $tc('mgd-ai-image-labels.settings.openEditor') }}
                </router-link>
            </sw-card>
        </sw-card-view>
    </template>
</sw-page>
{% endblock %}
`;var Ce="/_action/mgd-ai-image-labels/philosophy-page",x="Authentifizierter Administrationszugriff ist nicht verf\xFCgbar.",Pe="Die Philosophie-Seite konnte nicht vorbereitet werden.";async function Y(e,t){if(typeof e?.post!="function"||typeof t?.getBasicHeaders!="function")throw new TypeError(x);let i;try{i=t.getBasicHeaders()}catch{throw new TypeError(x)}if(i===null||typeof i!="object"||Array.isArray(i))throw new TypeError(x);try{return await e.post(Ce,{},{headers:i})}catch{throw new Error(Pe)}}var{Mixin:Ie}=Shopware,Te=/^[0-9a-f]{32}$/i;Shopware.Component.register("mgd-ai-settings-index",{template:W,inject:["acl","systemConfigApiService"],mixins:[Ie.getByName("notification")],data(){return{isLoading:!1,cmsPageId:null}},computed:{canPrepare(){return["system_config:update","cms_page:create","cms_section:create","cms_block:create","cms_slot:create"].every(e=>this.acl.can(e))},cmsPageRoute(){return this.cmsPageId?{name:"sw.cms.detail",params:{id:this.cmsPageId}}:null}},methods:{async preparePage(){if(!(!this.canPrepare||this.isLoading)){this.isLoading=!0,this.cmsPageId=null;try{let e=Shopware.Application.getContainer("init").httpClient,t=await Y(e,this.systemConfigApiService),i=t?.data?.cmsPageId;if(typeof t?.data?.created!="boolean"||typeof i!="string"||!Te.test(i))throw new TypeError("Unerwartete Antwortstruktur");this.cmsPageId=i,this.createNotificationSuccess({message:this.$tc(t.data.created?"mgd-ai-image-labels.settings.successCreated":"mgd-ai-image-labels.settings.successExisting")})}catch{this.createNotificationError({message:this.$tc("mgd-ai-image-labels.settings.error")})}finally{this.isLoading=!1}}}}});Shopware.Module.register("mgd-ai-settings",{type:"plugin",name:"MGD AI Image Labels",title:"mgd-ai-image-labels.settings.title",description:"mgd-ai-image-labels.settings.description",color:"#0870ff",icon:"regular-sparkles",routes:{index:{component:"mgd-ai-settings-index",path:"index",meta:{parentPath:"sw.settings.index",privilege:"system_config:update"}}},settingsItem:{group:"plugins",to:"mgd.ai.settings.index",icon:"regular-sparkles",privilege:"system_config:update"}});Shopware.Locale.extend("de-DE",T);Shopware.Locale.extend("en-GB",L);})();
