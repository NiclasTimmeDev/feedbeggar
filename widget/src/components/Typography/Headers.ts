import { DOMManipulator } from './../../services/Creator/DOMManipulator';

export class Headers {
  /**
   * The DOMManipulator object.
   */
  private domManipulator;

  /**
   * Constructor function.
   */
  public constructor() {
    this.domManipulator = new DOMManipulator();
  }

  public createH2(parent: HTMLElement, label: string): HTMLElement {
    const newEl = this.domManipulator.createDomElement({
      elementType: 'h2',
      innerHTML: label,
      classes: 'feedback-form--header',
      parent: parent,
    });

    return newEl;
  }
}
