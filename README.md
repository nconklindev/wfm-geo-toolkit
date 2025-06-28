[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2Fff5db798-80ab-4dd1-bf00-2b6122c379ea%3Flabel%3D1&style=flat-square)](https://forge.laravel.com/servers/928378/sites/2748508)

## Disclaimer ❗

This is an unofficial tool created to aid in the troubleshooting of geofencing issues that customers have while using
the Pro WFM mobile app. Since a plotting functionality has yet to be implemented, I created this tool as a way for
support technicians and consultants to work through issues while implementing or working with known places.

I am not a software developer and do this as a hobby. I do my best to make sure that everything works before pushing to
the main, but I'm only one person and cannot catch everything. That being said...**there will be bugs**.

With that out of the way, keep reading to learn more about what this is.

## What is this? 🤔

This is a Known Place Geofencing web application for plotting employee punches in the UKG Pro WFM mobile application.

## Who is this for? 🧩

It can be used by anyone, but registration has been restricted to UKG employees only at this time. I do not know if this
will change in the future.

## How do I use it?

Guests can simply go to https://wfmgeotoolkit.app and get started by going to **Tools > Plotter**. Or, by going directly
to the page at https://wfmgeotoolkit.app/tools/plotter. This tool is a very
basic coordinate
plotter that allows for search by address as well as manual entering of latitude and longitude coordinates. Enter all
required fields (though, it is recommended to change the color too), and click "Add to Plot." Plotted points will appear
in the "Plotted Points" table. Clicking on a row in the table will fly to that point and zoom in. Clicking the trashcan
icon will delete that point.

Registered users have access to much more within the application. At a high level, here is a list of some things
available:

- All CRUD operations are related to known places (Create, Read, Update, Delete)
- Create and edit known IP addresses
- Adding locations to known places
- Automatically generated business structure hierarchy based on locations added to known places
- Dashboard view that displays all created known places, known IP addresses, and locations
- Notifications for issues with known places and known IP addresses based on Pro WFM logic
- More!

A registered user can get their bearings by going to the `/welcome` page. Also, any page that requires authentication
will have a sidebar for easy navigation.

## I found a bug! 🪲

Fantastic. You can submit a bug over on the [Issues](https://github.com/nconklindev/wfm-geo-toolkit/issues) page. Click
the "New issue" button at the top and select "Bug Report." Fill out the required fields and submit the issue. I do not
guarantee any sort of response time with bug reports.

## I have an idea for a feature! ✨

Awesome! There's always room for improvement! I tried to consider everything and make this as feature-complete as
possible for a normal use-case, but if there's something you really want, then head over to
the [issues](https://github.com/nconklindev/wfm-geo-toolkit/issues) page and click "New issue" and select "Feature
Request." Fill out the required fields and submit the issue. I do not guarantee any response time with feature requests,
nor can I guarantee the feature will be accepted. Depending on scope, feasibility, and time commitment required, I may
not implement something that is suggested. Sorry in advance.

## Maintainers

@nconklindev

## Contributors

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tbody>
    <tr>
      <td align="center" valign="top" width="14.28%"><a href="https://github.com/bboisclair"><img src="https://avatars.githubusercontent.com/u/65306541?v=4?s=100" width="100px;" alt="Brandon Boisclair"/><br /><sub><b>Brandon Boisclair</b></sub></a><br /><a href="#bug-bboisclair" title="Bug reports">🐛</a></td>
      <td align="center" valign="top" width="14.28%"><a href="https://github.com/nconklindev"><img src="https://avatars.githubusercontent.com/u/190518646?v=4?s=100" width="100px;" alt="nconklindev"/><br /><sub><b>nconklindev</b></sub></a><br /><a href="#ideas-nconklindev" title="Ideas, Planning, & Feedback">🤔</a> <a href="#infra-nconklindev" title="Infrastructure (Hosting, Build-Tools, etc)">🚇</a> <a href="#code-nconklindev" title="Code">💻</a></td>
    </tr>
  </tbody>
</table>

<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->

<!-- ALL-CONTRIBUTORS-LIST:END -->

