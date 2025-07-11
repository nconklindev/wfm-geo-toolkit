/**
 * Global clipboard utilities
 */
window.clipboardUtils = {
    /**
     * Copy text to clipboard with visual feedback
     * @param {string} elementId - ID of element containing text to copy
     * @param {HTMLElement} button - Button element to show feedback on
     * @param {string} text - Optional direct text to copy (if no elementId)
     */
    copyToClipboard: function (elementId, button, text = null) {
        let textToCopy = text;

        // If no direct text provided, get it from element
        if (!textToCopy && elementId) {
            const element = document.getElementById(elementId);
            if (!element) {
                console.error('Element not found:', elementId);
                return;
            }
            textToCopy = element.textContent || element.innerText;
        }

        if (!textToCopy) {
            console.error('No text to copy');
            return;
        }

        // Check if Clipboard API is available
        if (!navigator.clipboard) {
            console.error('Clipboard API not supported in this browser');
            this.showCopyError(button);
            return;
        }

        // Use the modern Clipboard API
        navigator.clipboard
            .writeText(textToCopy)
            .then(() => {
                this.showCopySuccess(button);
            })
            .catch((err) => {
                console.error('Failed to copy text: ', err);
                this.showCopyError(button);
            });
    },

    /**
     * Show visual feedback for successful copy
     * @param {HTMLElement} button - Button element to show feedback on
     */
    showCopySuccess: function (button) {
        if (!button) return;

        // Store original state
        const originalIcon = button.querySelector('[data-flux-icon]');
        const originalIconName = originalIcon?.getAttribute('data-flux-icon');
        const originalClasses = button.className;

        // Change to check icon and add success styling
        if (originalIcon) {
            originalIcon.setAttribute('data-flux-icon', 'check');
        }
        button.classList.add('text-green-600');

        // Show tooltip
        this.showTooltip(button, 'Copied!', 'success');

        // Revert after 2 seconds
        setTimeout(() => {
            if (originalIcon && originalIconName) {
                originalIcon.setAttribute('data-flux-icon', originalIconName);
            }
            button.className = originalClasses;
        }, 2000);
    },

    /**
     * Show visual feedback for copy error
     * @param {HTMLElement} button - Button element to show feedback on
     */
    showCopyError: function (button) {
        if (!button) return;

        const originalClasses = button.className;
        button.classList.add('text-red-600');

        this.showTooltip(button, 'Copy failed', 'error');

        setTimeout(() => {
            button.className = originalClasses;
        }, 2000);
    },

    /**
     * Show a temporary tooltip
     * @param {HTMLElement} element - Element to show tooltip on
     * @param {string} message - Tooltip message
     * @param {string} type - Type of tooltip (success, error, info)
     */
    showTooltip: function (element, message, type = 'info') {
        // Remove any existing tooltips
        const existingTooltip = document.querySelector('.clipboard-tooltip');
        if (existingTooltip) {
            existingTooltip.remove();
        }

        // Create new tooltip
        const tooltip = document.createElement('div');
        tooltip.className = `clipboard-tooltip fixed z-[9999] px-2 py-1 text-xs rounded shadow-lg pointer-events-none whitespace-nowrap ${
            type === 'success'
                ? 'bg-green-600 text-white'
                : type === 'error'
                  ? 'bg-red-600 text-white'
                  : 'bg-zinc-800 text-white'
        }`;
        tooltip.textContent = message;

        // Start with opacity 0 for smooth transition
        tooltip.style.opacity = '0';
        tooltip.style.transition = 'opacity 150ms ease-in-out';

        // Get element position relative to viewport
        const rect = element.getBoundingClientRect();

        // Position tooltip just above the button center
        const left = rect.left + rect.width / 2;
        const top = rect.top - 8; // Just 8px above the button

        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
        tooltip.style.transform = 'translateX(-50%) translateY(-100%)';

        document.body.appendChild(tooltip);

        // Force reflow and then fade in
        tooltip.offsetHeight;
        requestAnimationFrame(() => {
            tooltip.style.opacity = '1';
        });

        // Remove after delay
        setTimeout(() => {
            if (tooltip.parentNode) {
                tooltip.style.opacity = '0';
                setTimeout(() => {
                    if (tooltip.parentNode) {
                        tooltip.remove();
                    }
                }, 150);
            }
        }, 1200);
    },
};

// Global convenience function
window.copyToClipboard = window.clipboardUtils.copyToClipboard.bind(window.clipboardUtils);
