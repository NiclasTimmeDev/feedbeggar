import { NewElementInterface } from './NewElementInterface';

export interface DOMManipulatorInterface {
  createDomElement(newElement: NewElementInterface): HTMLElement;
}
