import { DOMManipulator } from './../../services/Creator/DOMManipulator';

/**
 * Provides a link to Feedbeggar like so:
 *
 * "Powered by Feedbeggar"
 */
export class PoweredBy {
    /**
     * The dom manipulator used to create HTML elements.
     */
    private domManipulator;

    /**
     * The constructor function.
     */
    public constructor() {
        this.domManipulator = new DOMManipulator();

        this.createPoweredByLink();
    }

    /**
     * Create the link in the form of
     * "Created by Feedbeggar"
     */
    public createPoweredByLink = () => {
        const text = this._createText();

        if (!text) {
            return;
        }

        const link = this._createLink(text);

    };

    /**
     * Create the link element.
     *
     * @param parent
     *   The parent element.
     */
    private _createLink = (parent: HTMLElement): any => {
        const link = this.domManipulator.createDomElement({
            elementType: 'a',
            classes: 'feedbeggar-powered-by--link',
            innerHTML: 'Feedbeggar',
            parent: parent
        });

        link.setAttribute('href', 'https://feedbeggar.com');
        link.setAttribute('rel', 'follow');
        link.setAttribute('target', '_blank');

        return link;
    }

    /**
     * Create the text element.
     */
    private _createText = () => {
        const feedbackForm: HTMLElement | null = document.querySelector('.feedback-form--inner');

        if (!feedbackForm) {
            return;
        }

        return this.domManipulator.createDomElement({
            elementType: 'div',
            classes: 'feedbeggar-powered-by--text',
            innerHTML: 'Powered by ',
            parent: feedbackForm
        });
    }
}
