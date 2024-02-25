import { Controller } from '@hotwired/stimulus';
import { Toast } from 'bootstrap';

/**
 * @class FlashMessagesController
 */
export default class extends Controller {
    /**
     * Connect
     */
    connect() {
        const toasts = this.element.querySelectorAll('.toast');
        toasts.forEach((toast) => {
            (new Toast(toast, { delay: 5000 })).show();
        });
    }
}
