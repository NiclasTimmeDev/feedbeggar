import { DOMManipulator } from "./services/Creator/DOMManipulator";
import { FeedbackForm } from "./components/Form/FeedbackForm";
import { URLParser } from "./services/URL/UrlParser";
import "./config/axiosConfig.ts";
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
domManipulator.createDomElement({
  elementType: "div",
  parent: document.querySelector("body"),
  innerHTML: "Feedback",
  classes: "anchor"
});

// Make body overflow hidden so we can have the transition animation when opening the widget.
const body = document.querySelector("body");
if(body) {
  body.style.overflow = "hidden"
}

// Create the form element.
const feedbackForm = new FeedbackForm(projectID);
