<modal-window v-bind:do-active="modalStates.mailPreview"
              v-bind:scrolled-position-y="posY"
              id="#cmp-modal-mailPreview">
    <select-sync-content
        v-bind:root-class="''"
        v-bind:ref-select="'msgSelect'"
        v-bind:initial-item="{{ json_encode(['text' => '', 'value' => '', 'subject' => '']) }}">
        <mail-preview-panel
            slot="content"
            slot-scope="item"
            ref="mailPreview"
            v-bind:base-url="'{{ $previewUrl ?? route('notice.message.preview') }}'"
            v-bind:is-active="modalStates.mailPreview"
            v-bind:form-id="'mailPreviewForm'"
            v-bind:initial-form-items="{{ json_encode(['_token' => csrf_token()]) }}"
            v-bind:message-id="item.item.value"
            v-bind:subject="item.item.subject">
        </mail-preview-panel>
    </select-sync-content>
</modal-window>
