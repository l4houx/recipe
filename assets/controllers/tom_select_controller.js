import { Controller } from '@hotwired/stimulus';
import TomSelect from '../vendor/tom-select/tom-select.index.js';

/**
 * @class TomSelectController
 */
export default class extends Controller {
    /**
     * Connect
     */
    connect() {
        if (this.element.classList.contains('#sortable-select')) return;
        TomSelect(this.element);
    }
}
