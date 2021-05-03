import { DOMManipulator } from './../../services/Creator/DOMManipulator';
export class Button {
  /**
   * The dom manipulator used to create HTML elements.
   */
  private domManipulator;

  /**
   * The constructor function.
   */
  public constructor() {
    this.domManipulator = new DOMManipulator();
  }

  public createPrimaryButton(
    parent: HTMLElement,
    label: string,
    eventListenerFunc: CallableFunction,
  ) {
    const btn = this.domManipulator.createDomElement({
      elementType: 'button',
      classes: 'feedback-form--primary-btn',
      parent: parent,
      innerHTML: label,
      eventListenerType: 'click',
      //eventListenerFunc: eventListenerFunc,
    });
  }
}
