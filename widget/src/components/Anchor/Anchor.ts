import { DOMManipulator } from './../../services/Creator/DOMManipulator';

export class Anchor {
    /**
     * The dom manipulator used to create HTML elements.
     *
     * @private
     */
    private domManipulator;

    /**
     * Determines if the form should be shown or not.
     *
     * @private
     */
    private showForm: boolean;

    private anchor: HTMLElement;

    /**
     * The constructor function.
     */
    public constructor() {
        this.domManipulator = new DOMManipulator();
        this.showForm = false;
        this.anchor = this.createAnchorElement();
    }

    /**
     * Create the anchor element and append it to the body.
     */
    public createAnchorElement (): HTMLElement {
        // Create the anchor element.
        const anchor =  this.domManipulator.createDomElement({
            elementType: "div",
            parent: document.querySelector("body"),
            innerHTML: "Feedback",
            classes: "feedbeggar--anchor",
            eventListenerType: 'click',
            eventListenerFunc: this._toggleForm
        });

        anchor.setAttribute('data-html2canvas-ignore', 'true');

        return anchor;
    }

    /**
     * Toggle the form on click of the anchor element.
     * @private
     */
    private _toggleForm() {
        const form: Element | null = document.querySelector('.feedback-form.feedback-form--wrapper');
        const anchor = document.querySelector('.feedbeggar--anchor');

        if (!form || !anchor) {
            return false;
        }

        // Show the form if it was hidden thus far.
        if (!this.showForm) {
            form.classList.remove('feedbeggar--feedback-form-hidden');
            form.classList.add('feedbeggar--feedback-form-shown');

            // Also move the anchor element.
            anchor.classList.add('feedbeggar-anchor-shift-left');
        }

        // Hide the form if it was visible so far.
        if (this.showForm) {
            form.classList.add('feedbeggar--feedback-form-hidden');
            form.classList.remove('feedbeggar--feedback-form-shown');

            // Also move the anchor element.
            anchor.classList.remove('feedbeggar-anchor-shift-left');
        }

        // Toggle form state.
        this.showForm = !this.showForm;
    }
}
