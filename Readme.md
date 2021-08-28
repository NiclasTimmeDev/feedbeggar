# feedbeggar

## What's inside?

Feedbeggar is an open source Full-Stack app to collect user-feedback from your website. It allows you to embed a widget on your website with with just a single line of code, which enables your users to submit feedback from your website. You can then access and organize the feedback on your own dashboard.

## Why use Feedbeggar

- It's free and open source.
- It's batteries included: Everything you need is already inside. All you have to do is host it and embed the widget on your website.
- It's production ready: Thanks to the production-ready dockerization you don't even have to worry about server setup.
- It's beautiful: The dasboard uses Vuetify as a component library, giving it a great user experience.

## Features

### Widget

- The widget is comletely written in TypeScript and can therefore be easily embedded on your website instead of pasting a large chuck of HTML into yur code.
- On the website you embed it on, it will appear as a small widget on the right side of the screen which says "Feedback"
- Users can open that drawer and send feedback
- Users can even paint on the screen and attach a screenshot to show you the problem in more detail
- You (the owner) will be notified by mail when a feedback is submitted

### Dashboard

- The dasboard is where you can access all the feedback you received
- You can create multiple projects so you can collect feedback from multiple websites if you want to
- You can create so called "buckets" that allow you to group the feedback you received. For example, one feedback might be something like "I don't like the background color of XY" and another feedback might be "Why not change the background to green?" Although these are two different feedbacks, they basically ask for the same. So you might want to create a bucket named "Change background color" and put both feedbacks in there.
- You can answer the people who gave feedback per mail

## Tech-Stack

- Widget: TypeScript
- Backend-API: PHP/Laravel
- Dashboard-Frontend: Vue/NuxtJS
- Image storage: AWS S3
- Payment: Stripe

## Want to use feedbeggar commercially?

Go ahead, I don't mind... I even implemented Stripe to make it easier for you to use Feedbeggar as a SaaS.

## Screenshots

### Login

![Login](/docs/images/login.png 'Login to your account')

### Create and update your projects

![Projects](/docs/images/update_project.png 'Create or update a project')

### Your feedback inbox

![Inbox](/docs/images/inbox.png 'Your feedback inbox')

### Detailed information on a feedback submission

![Feedback detail](/docs/images/feedback_detail.png 'See detail of your feedback')

### Create a new bucket

![Create a new bucket](/docs/images/login.png 'Create a new bucket')

### Documention of usage and terms.

![Documentatino](/docs/images/login.png 'Documentation')
