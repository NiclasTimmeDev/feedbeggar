export class URLParser {
  /**
   * Extraxt query strings from url of the javascript file.
   * @returns
   */
  static getIdFromUrlQuery = (): number | undefined => {
    // Get all script tags from the DOM.
    const scripts = <HTMLCollectionOf<HTMLScriptElement>>(
      document.getElementsByTagName('script')!
    );

    // By the time this script is executed, it is the last in the list.
    const myScript = <HTMLScriptElement>scripts[scripts.length - 1]!;
    if (!myScript) {
      return;
    }

    // Split the url by "?".
    const src = <string[]>myScript.getAttribute('src')?.split('?');

    // Only continue if split was successful.
    if (!Array.isArray(src)) {
      return;
    }

    const queryString = <string>src[1];

    // The script only works if one query was provided.
    const hasMultipleQueries =
      Array.isArray(queryString.split('&')) &&
      queryString.split('&').length > 1;
    if (hasMultipleQueries) {
      return;
    }

    const keyValuePair = <string[]>queryString.split('=');

    // Check that the query param is actually 'id'.
    if (keyValuePair[0] !== 'id') {
      return;
    }

    // Convert id to number.
    const id = parseInt(keyValuePair[1]);
    return id;
  };

  /**
   * Get the current pathname from the URL.
   * @returns
   */
  static getCurrentPathname(): string {
    return window.location.pathname;
  }

  /**
   * Get the current URL.
   * @returns
   */
  static getCurrentUrl(): string {
    return window.location.href;
  }
}
