import { config } from '../../config/config';
import { DOMManipulator } from './../../services/Creator/DOMManipulator';

/**
 * The class to create dropdown (select) elements.
 */
export class Dropdown {
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

  /**
   * The function to create the dropdown menu that displays the feedback types.
   *
   * @returns HTMLElement
   */
  public createDropdown(): HTMLElement {
    // Get the feedback options from the config file.
    const options = config.feedbackOtions;

    // Create new HTML select element.
    const dropDown = this.domManipulator.createDomElement({
      elementType: 'select',
      classes: 'feedback-form--dropdown',
    });

    // Append all options to the select element.
    options.forEach((option: string) => {
      const newOption = this.domManipulator.createDomElement({
        elementType: 'option',
      });

      newOption.innerHTML = option;

      dropDown.appendChild(newOption);
    });

    return dropDown;
  }
}
