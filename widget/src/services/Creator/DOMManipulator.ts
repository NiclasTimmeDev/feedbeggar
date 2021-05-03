import { NewElementInterface } from './NewElementInterface';
import { DOMManipulatorInterface } from './DOMManipuatorInterface';

/**
 * A class to manipulate the DOM.
 */
export class DOMManipulator implements DOMManipulatorInterface {
  /**
   *
   * @param {NewElementInterface} newElement
   *   The element that will be created;
   *
   * @returns HTMLElement
   */
  public createDomElement(newElement: NewElementInterface): HTMLElement {
    const el = document.createElement(newElement.elementType);

    // Set the innerHTML, if given.
    if (newElement.innerHTML) {
      el.innerHTML = newElement.innerHTML;
    }

    // Add classes if given.
    if (newElement.classes) {
      el.className = newElement.classes;
    }

    // Add event listener if given.
    if (newElement.eventListenerType && newElement.eventListenerFunc) {
      el.addEventListener(
        // Event Listener type.
        newElement.eventListenerType,
        // Event Listener function.
        newElement.eventListenerFunc,
      );
    }

    // Append or prepend to another HTML element, if given.
    if (newElement.parent) {
      if (newElement.prepend) {
        newElement.parent?.prepend(el);
      } else {
        newElement.parent?.appendChild(el);
      }
    }

    return el;
  }
}
