[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2Fff5db798-80ab-4dd1-bf00-2b6122c379ea%3Flabel%3D1&style=flat-square)](https://forge.laravel.com/servers/928378/sites/2748508)

## Disclaimer ❗

This project is an independent, unofficial tool developed by community members. It is not affiliated with, endorsed, sponsored, or supported by Ultimate Kronos Group (UKG) or any of its subsidiaries. 

- This tool is provided "as is" without warranty of any kind
- UKG is not responsible for any issues arising from its use
- The tool is supported and maintained by aforementioned community members
- All trademarks belong to their respective owners

With that out of the way, keep reading to learn more about what this is.

## What is this? 🤔

This started as a tool to aid in the troubleshooting of geofencing issues that users of Pro WFM face while using the companion mobile app. Since then, it has evolved to be a broader toolkit that includes other functionality to help make the user experience smoother and easier to troubleshoot. 

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

## Can I help? 🤝

Sure! Follow the guidelines for the corresponding thing you want to help with.

### Bugs 🐛

You can submit a bug over on the [Issues](https://github.com/nconklindev/wfm-geo-toolkit/issues) page. Click
the "New issue" button at the top and select "Bug Report." Fill out the required fields and submit the issue. I do not
guarantee any sort of response time with bug reports.

### Features ✨

You can submit a Feature Request using the provided [form](https://github.com/nconklindev/wfm-geo-toolkit/issues/new?template=feature_request.yml).

If you want to work on a suggested feature, please see the [contribution guidelines](/.github/CONTRIBUTING.md).

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

