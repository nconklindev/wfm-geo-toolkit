## Disclaimer ❗

This is an unofficial tool created to aid in the troubleshooting of geofencing issues that customers have while using
the Pro WFM mobile app. Since a plotting functionality has yet to be implemented, I created this tool as a way for
support technicians and consultants to work through issues while implementing or working with known places.

I am not a software developer and do this as a hobby. I do my best to make sure that everything works before pushing to
main, but I'm only one person and cannot catch everything. That being said...**there will be bugs**.

With that out of the way, keep reading to learn more about what this is.

## What is this? 🤔

This is a Known Place Geofencing web application for plotting employee punches in the UKG Pro WFM mobile application.

## Who is this for? 🧩

It can be used by anyone, but registration has been restricted to UKG employees only at this time. I do not know if this
will change in the future.

## How do I use it?

Guests can simply go to [URL] and get started by going to **Tools > Plotter**. This tool is a very basic coordinate
plotter that allows for search by address as well as manual entering of latitude and longitude coordinates. Enter all
required fields (though, it is recommended to change the color too), and click "Add to Plot". Plotted points will appear
in the "Plotted Points" table. Clicking on a row in the table will fly to that point and zoom in. Clicking the trashcan
icon will delete that point.

Registered users have access to much more within the application. At a high-level, here is a list of some things
available:

- All CRUD operations related to known places (Create, Read, Update, Delete)
- Create,
- Adding locations to known places
- Automatically generated business structure hierarchy based on locations added to known places
- Dashboard view that displays all created known places, known IP addresses and locations
- Notifications for issues with known places and known IP addresses based on Pro WFM logic
- More!

A registered user can get their bearings by going to the `/welcome` page. Also, any page that requires authentication
will have a sidebar for easy navigation.

## I found a bug! 🪲

Fantastic. You can submit a bug over on the [Issues](https://github.com/nconklindev/wfm-geo-toolkit/issues) page. Click
the "New issue" button at the top and select "Bug Report". Fill out the required fields and submit the issue. I do not
guarantee any sort of response time with bug reports.

## I have an idea for a feature! ✨

Awesome! There's always room for improvement! I tried to consider everything and make this as feature-complete as
possible for normal use-case, but if there's something you really want, then head over to
the [issues](https://github.com/nconklindev/wfm-geo-toolkit/issues) page and click "New issue" and select "Feature
Request". Fill out the required fields and submit the issue. I do not guarantee any response time with feature requests,
nor can I guarantee the feature will be accepted. Depending on scope, feasibility, and time commitment required, I may
not implement something that is suggested. Sorry in advance.

## Maintainers

@nconklindev
