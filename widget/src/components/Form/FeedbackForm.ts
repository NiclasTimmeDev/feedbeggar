import { DOMManipulator } from '../../services/Creator/DOMManipulator';
import { InpuElementInterface } from './Interfaces/InputElementInterface';
import { TextFieldInterface } from './Interfaces/TextFieldInterface';
import { Headers } from './../Typography/Headers';
import { Dropdown } from './Dropdown';
import { Helpers } from './../../services/Helpers/Helpers';
import { ScreenshotHandler } from './../../services/Canvas/ScreenshotHandler';
import { ClientInfo } from './../../services/Client/ClientInfo';
import axios from 'axios';
import {PoweredBy} from "../PoweredBy/PoweredBy";
/**
 * The class to create the feedback form element.
 */
export class FeedbackForm {
  /**
   * The DOMManipulator object.
   */
  private domManipulator;

  /**
   * The header object.
   */
  private headerManager;

  /**
   * The dropdown manager.
   */
  private dropdownManager;

  /**
   * The project id. Identifies the user.
   */
  protected projectId;

  /**
   * The form errors.
   */
  public formErrors = {
    option: '',
    email: '',
    text: '',
  };

  /**
   * Constructor function.
   */
  public constructor(projectId: number) {
    // Get the project id. Will be necessary when submitting the form.
    this.projectId = projectId;

    // Used to create new HTML elements.
    this.domManipulator = new DOMManipulator();

    // Used to creatae h-tag HTML elements.
    this.headerManager = new Headers();

    // Used to create dropdown (select) elements.
    this.dropdownManager = new Dropdown();

    // Create the form.
    this.createFeedbackForm();
  }

  /**
   * Create the feedback form dom elements.
   */
  public createFeedbackForm = (): void => {
    // Create wrapper element.
    const wrapper = this.domManipulator.createDomElement({
      elementType: 'div',
      parent: document.querySelector('body'),
      classes: 'feedback-form feedback-form--wrapper feedbeggar--feedback-form-hidden',
    });

    /**
     * Set a new data attribute so the widget will be ignored
     * when taking a screenshot with html2canvas.
     */
    wrapper.setAttribute('data-html2canvas-ignore', 'true');

    // Create form and append it to wrapper.
    const feedbackForm = this.domManipulator.createDomElement({
      elementType: 'form',
      parent: wrapper,
      classes: 'feedback-form feedback-form--inner',
      eventListenerType: 'submit',
      eventListenerFunc: this.submit,
    });

    // Add the header element.
    this.headerManager.createH2(feedbackForm, 'Give us feedback!');

    // The dropdown menu.
    const dropdown = this.dropdownManager.createDropdown();
    feedbackForm.appendChild(dropdown);

    // If present, remove error message when value changes.
    dropdown.addEventListener('change', () => {
      this.removeErrorMsg(dropdown);
      this.formErrors.option = '';
    });

    // Create email field.
    const emailInputField = this.createInputElement({
      type: 'text',
      placeholder: 'Your email address',
      name: 'email',
      parent: feedbackForm,
      classes: 'feedback-form--email',
    });

    // If present, remove error message when value changes.
    emailInputField.addEventListener('input', () => {
      this.removeErrorMsg(emailInputField);
      this.formErrors.email = '';
    });

    // The text area.
    const textField = this.createTextField({
      name: 'text',
      placeholder: 'What is on your mind?',
      rows: 3,
      parent: feedbackForm,
      classes: 'feedback-form--text',
    });

    // If present, remove error message when value changes.
    textField.addEventListener('input', () => {
      this.removeErrorMsg(textField);
      this.formErrors.text = '';
    });

    // Wrapper for screenshot attachment element.
    const screenshotWrapper = document.createElement('div');
    screenshotWrapper.className ='feedbeggar-screenshot-form-wrapper';

    feedbackForm.appendChild(screenshotWrapper);

    // Create checkbox for screenshot.
    const checkbox = this.createInputElement({
      type: 'checkbox',
      name: 'screenshot-checkbox',
      placeholder: 'Screenshot Checkbox',
      parent: screenshotWrapper,
      classes: 'feedback-form--checkbox',
    });
    checkbox.id = 'feedback-form--checkbox';

    const checkBoxLabel = <HTMLLabelElement>document.createElement('label');
    checkBoxLabel.htmlFor = 'feedback-form--checkbox';
    checkBoxLabel.innerHTML = 'Attach screenshot';
    checkBoxLabel.className = 'feedback-form--checkbox-label';
    screenshotWrapper.appendChild(checkBoxLabel);

    // Toggle paint functionality of checkbox on click.
    this.attachPaintFunctionality(checkbox);

    // Submit button.
    this.createSubmitButton(feedbackForm, 'Submit Feedback');

    this.poweredByLink();
  };

  /**
   *
   * @param {InpuElementInterface} element
   *   The data about the input element that will be created.
   *
   * @returns {HTMLInputElement}
   *   The newly created Input Element
   */
  public createInputElement(element: InpuElementInterface): HTMLInputElement {
    // Create new element.
    const inputEl = document.createElement('input');

    // Set meta data.
    inputEl.type = element.type;
    inputEl.placeholder = element.placeholder;
    inputEl.name = element.name;

    // Apply it css classes if given.
    if (element.classes) {
      inputEl.className = element.classes;
    }

    // Append it to parent.
    element.parent.appendChild(inputEl);
    return inputEl;
  }

  /**
   * Crete TextField Element.
   */
  public createTextField(element: TextFieldInterface): HTMLTextAreaElement {
    // Create new element.
    const textEl = document.createElement('textarea');

    // Add properties.
    textEl.placeholder = element.placeholder;
    textEl.name = element.name;
    textEl.rows = element.rows;

    // Add css classes only if provided.
    if (element.classes) {
      textEl.className = element.classes;
    }

    // Append to parent if provided.
    if (element.parent) {
      element.parent.appendChild(textEl);
    }
    return textEl;
  }

  /**
   *
   * @param {HTMLElement} parent
   *   The parent element of the button.
   *
   * @param {string} label
   *   The label of the button.
   */
  public createSubmitButton(parent: HTMLElement, label: string): void {
    const btn = document.createElement('input');

    btn.type = 'submit';
    btn.className = 'feedback-form--btn';
    btn.value = label;

    parent.appendChild(btn);
  }

  /**
   *
   * @param {Event} e
   *   The event object.
   */
  public submit = async (e: Event): Promise<boolean> => {
    e.preventDefault();

    const option = document.querySelector<HTMLSelectElement>(
      '.feedback-form--dropdown',
    )!;

    const email = document.querySelector<HTMLInputElement>(
      '.feedback-form--email',
    )!;

    const text = document.querySelector<HTMLTextAreaElement>(
      '.feedback-form--text',
    )!;

    const checkbox = document.querySelector<HTMLInputElement>(
      '.feedback-form--checkbox',
    )!;

    // TODO: Check if they are valid.
    // Check if option was selected.
    if (!option.value) {
      this.formErrors.option = 'Please select an option.';

      // Display error message.
      this.displayErrorMsg(option, this.formErrors.option);
      return false;
    }
    this.formErrors.option = '';

    // Email is optional. However, if something is entered in the field, make sure it's an email.
    if (email.value !== '') {
      const isEmail = Helpers.validateEmail(email.value);

      if (!isEmail) {
        this.formErrors.email =
          'Please make sure the given email address is valid.';

        // Insert error message.
        this.displayErrorMsg(email, this.formErrors.email);
        return false;
      }
    }
    this.formErrors.email = '';

    // Check the text field.
    if (!text.value) {
      this.formErrors.text = 'Please fill in the text area.';
      this.displayErrorMsg(text, this.formErrors.text);
      return false;
    }
    this.formErrors.text = '';

    // Because of paranoia, make second check if there are no errors.
    if (
      this.formErrors.email ||
      this.formErrors.option ||
      this.formErrors.text
    ) {
      return false;
    }

    // If the corresponding checkbox is checked, make a screenshot.
    let screenshot = <string>'';
    if (checkbox.checked) {
      screenshot = await ScreenshotHandler.takeScreenshot();
    }

    // Create object with data that will be sent to the api.
    const data = {
      projectId: this.projectId,
      type: option.value,
      email: email.value || null,
      text: text.value,
      screenshot: screenshot || null,
      browserInfo: ClientInfo.getBrowserData(),
      osName: ClientInfo.getOSInfo(),
      path: window.location.pathname,
    };

    try {
      const res = await axios.post('api/feedback', data);
    } catch (error) {
      console.log(error);
    }
    return true;
  };

  /**
   *
   * @param element
   */
  public displayErrorMsg = (element: HTMLElement, error: string) => {
    element.classList.add('feedback-form--error');

    /**
     * Check if a error message already exists.
     * If so, adapt the error message and return.
     */
    const sibling = element.nextElementSibling!;
    if (
      sibling &&
      sibling.nodeName === 'SPAN' &&
      sibling.classList.contains('feedback-form--error-msg')
    ) {
      sibling.innerHTML = error;
      return;
    }

    // Create new element that contains the error message.
    const msg = this.domManipulator.createDomElement({
      elementType: 'span',
      classes: 'feedback-form--error-msg',
      innerHTML: error,
    });

    // Insert new span right after the element where the error occurs.
    element.parentNode?.insertBefore(msg, element.nextSibling);
  };

  public removeErrorMsg = (element: HTMLElement) => {
    // Remove the red border from the element.
    element.classList.remove('feedback-form--error');

    // Get the span that contains the error message.
    const sibling = element.nextElementSibling!;

    // ONLY if the sibling is actually an error message, remove it from the DOM.
    if (
      sibling &&
      sibling.nodeName === 'SPAN' &&
      sibling.classList.contains('feedback-form--error-msg')
    ) {
      sibling.remove();
    }
  };

  public attachPaintFunctionality = (element: HTMLInputElement) => {
    element.addEventListener('change', function () {
      if (this.checked) {
        ScreenshotHandler.painOnScreen();
      } else {
        ScreenshotHandler.removePaintOnScreenElement();
      }
    });
  };

  public poweredByLink = () => {
    const poweredBy = new PoweredBy();
  }
}
