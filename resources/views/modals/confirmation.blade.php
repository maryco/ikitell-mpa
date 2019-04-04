<modal-window v-bind:do-active="modalStates.confirmation"
              v-bind:scrolled-position-y="posY"
              id="#cmp-modal-confirmation">
    <confirmation-panel
        ref="confirmationPanel"
        v-bind:label="{{ json_encode(['ok' => __('label.btn.ok'), 'cancel' => __('label.btn.cancel')])}}">
    </confirmation-panel>
</modal-window>
