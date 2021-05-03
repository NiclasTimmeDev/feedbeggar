import { FeedbackDataInterface } from './FeedbackDataInterface';
import { URLParser } from './../URL/UrlParser';
import { ClientInfo } from './../Client/ClientInfo';
import { BrowserInfoInterface } from './../Client/BrowserInfoInterface';
import axios from 'axios';

/**
 * A class to interact with the api.
 */
export class Connector {
  /**
   * Submit feedback to the api.
   *
   * @param {FeedbackDataInterface} feedbackData
   */
  static submit = async (feedbackData: FeedbackDataInterface) => {
    // Get the current path.
    const path = <string>URLParser.getCurrentPathname();

    // Get the current browser info.
    const browserInfo = <BrowserInfoInterface>ClientInfo.getBrowserData();

    // Get the operating system of the user.
    const OS = ClientInfo.getOSInfo();

    try {
      // Send data to api.
      const res = await axios.post('/', {
        id: feedbackData.id,
        type: feedbackData.type,
        email: feedbackData.email || null,
        path: path,
        text: feedbackData.text,
        browserInfo,
        os: OS,
      });

      if (res.status === 201) {
        // TODO: Display success message to user.
      }
    } catch (error) {
      console.log(error);
      // TODO: Display error message to user.
    }
  };
}
