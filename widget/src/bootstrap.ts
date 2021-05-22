import { DOMManipulator } from "./services/Creator/DOMManipulator";
import { FeedbackForm } from "./components/Form/FeedbackForm";
import { URLParser } from "./services/URL/UrlParser";
import "./config/axiosConfig.ts";
import {Anchor} from "./components/Anchor/Anchor";
/**
 * Only proceed if we have a valid project id
 * in the query string of our script tag in the HTML file.
 */
const projectID = <number>URLParser.getIdFromUrlQuery();

if (!projectID) {
  throw new Error(
    "Invalid URL for Feedback Collector: The query string is invalid."
  );
}

const domManipulator = new DOMManipulator();

// Create the anchor element.
const anchor = new Anchor();

// Make body overflow hidden so we can have the transition animation when opening the widget.
const body = document.querySelector("body");
if(body) {
  body.style.overflowX = "hidden"
}

// Create the form element.
const feedbackForm = new FeedbackForm(projectID);
