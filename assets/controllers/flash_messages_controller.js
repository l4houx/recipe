import { Controller } from '@hotwired/stimulus';
import { Toast } from '../vendor/bootstrap/bootstrap.index.js';

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
