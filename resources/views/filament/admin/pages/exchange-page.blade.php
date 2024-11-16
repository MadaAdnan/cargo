<x-filament-panels::page>

    <form wire:submit.prevent="submit">
        {{ $this->form }}
        <button x-mousetrap.global.mod-s=""
                x-data="{
            form: null,
            isUploadingFile: false,
        }"
                x-init="
            form = $el.closest('form')

            form?.addEventListener('file-upload-started', () => {
                isUploadingFile = true
            })

            form?.addEventListener('file-upload-finished', () => {
                isUploadingFile = false
            })
        "
                x-bind:class="{ 'enabled:opacity-70 enabled:cursor-wait': isUploadingFile }"
                style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                class="fi-btn my-2 relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                type="submit" wire:loading.attr="disabled" x-bind:disabled="isUploadingFile">
            <!--[if BLOCK]><![endif]-->        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->

            <!--[if BLOCK]><![endif]-->
            <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                 class="animate-spin fi-btn-icon transition duration-75 h-5 w-5 text-white"
                 wire:loading.delay.default="" wire:target="create">
                <path clip-rule="evenodd"
                      d="M12 19C15.866 19 19 15.866 19 12C19 8.13401 15.866 5 12 5C8.13401 5 5 8.13401 5 12C5 15.866 8.13401 19 12 19ZM12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                      fill-rule="evenodd" fill="currentColor" opacity="0.2"></path>
                <path d="M2 12C2 6.47715 6.47715 2 12 2V5C8.13401 5 5 8.13401 5 12H2Z" fill="currentColor"></path>
            </svg>
            <!--[if ENDBLOCK]><![endif]-->

            <!--[if BLOCK]><![endif]-->
            <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                 class="animate-spin fi-btn-icon transition duration-75 h-5 w-5 text-white" x-show="isUploadingFile"
                 style="display: none;">
                <path clip-rule="evenodd"
                      d="M12 19C15.866 19 19 15.866 19 12C19 8.13401 15.866 5 12 5C8.13401 5 5 8.13401 5 12C5 15.866 8.13401 19 12 19ZM12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                      fill-rule="evenodd" fill="currentColor" opacity="0.2"></path>
                <path d="M2 12C2 6.47715 6.47715 2 12 2V5C8.13401 5 5 8.13401 5 12H2Z" fill="currentColor"></path>
            </svg>
            <!--[if ENDBLOCK]><![endif]-->
            <!--[if ENDBLOCK]><![endif]-->

            <span x-show="! isUploadingFile" class="fi-btn-label">
        إضافة
    </span>

            <!--[if BLOCK]><![endif]--> <span x-show="isUploadingFile" class="fi-btn-label" style="display: none;">
            جاري رفع الملف...
        </span>
            <!--[if ENDBLOCK]><![endif]-->

            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->

            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
        </button>
    </form>

</x-filament-panels::page>
