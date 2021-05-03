export interface NewElementInterface {
  elementType: string;
  parent?: HTMLElement | null;
  prepend?: boolean;
  innerHTML?: string;
  classes?: string;
  eventListenerType?: string;
  eventListenerFunc?: EventListenerOrEventListenerObject;
}
