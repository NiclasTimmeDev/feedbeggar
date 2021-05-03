/**
 * A helper class providing usefull small methods.
 */
export class Helpers {
  /**
   * Validate an email via RegEx.
   *
   * @param {string} email
   *   The email that will be tested.
   *
   * @returns boolean
   *   True if the email is valid.
   */
  static validateEmail(email: string): boolean {
    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
  }
}
