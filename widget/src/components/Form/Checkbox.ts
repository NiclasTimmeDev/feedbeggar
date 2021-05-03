import { DOMManipulator } from './../../services/Creator/DOMManipulator';

export class Checkbox {
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

  public createCheckBox = () => {
    const dropDown = this.domManipulator.createDomElement({
      elementType: 'select',
      classes: 'feedback-form--dropdown',
    });
  };
}
