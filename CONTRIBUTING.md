## Contribute To Sugar Calendar

Community made patches, localizations, bug reports and contributions are always welcome and are crucial to ensure Sugar Calendar remains the #1 event calendar for WordPress.

When contributing, please ensure you follow the guidelines below so that we can keep on top of things.

__Please Note:__ GitHub is for bug reports and contributions only - if you have a support question or a request for a customization don't post here; go to our [Support page](https://sugarcalendar.com/support/) instead.

## Getting Started

* __Do not report potential security vulnerabilities here. Email them privately to our security team at [security@sugarcalendar.com](mailto:security@sugarcalendar.com)__
* Before submitting a ticket, please be sure to replicate the behavior with no other plugins active and on a base theme like Twenty Twenty.
* Submit a ticket for your issue, assuming one does not already exist.
	* Raise it on our [Issue Tracker](https://github.com/sugarcalendar/lite/issues)
	* Clearly describe the issue including steps to reproduce the bug.
	* Make sure you fill in the earliest version that you know has the issue as well as the version of WordPress you're using.

## Making Changes

* Fork the repository on GitHub
* Make the changes to your forked repository
	* Ensure you stick to the [WordPress Coding Standards](https://codex.wordpress.org/WordPress_Coding_Standards)
* When committing, reference your issue (if present) and include a note about the fix
* If possible, and if applicable, please also add/update unit tests for your changes
* Push the changes to your fork and submit a pull request to the proper branch of the repository (likely master, but maybe a `release/` branch)

## Code Documentation

* We ensure that every function is documented well and follows the standards set by phpDoc
* An example function can be found [here](https://github.com/sugarcalendar/lite/blob/master/sugar-event-calendar/includes/events/functions.php#L138)
* Please make sure that every function is documented so that when we update our API Documentation things don't go awry!
	* If you're adding/editing a function in a class, make sure to add `@access {private|public|protected}`
* Finally, please use tabs and not spaces. The tab indent size should be 4 for all languages: PHP, JavaScript, and CSS.

At this point you're waiting on us to merge your pull request. We'll review every pull request and make suggestions or changes if necessary.

## Developer Certificate of Origin
By contributing to Sugar Calendar, you agree to the Developer Certificate of Origin.

In its simplest form, the DCO states that you have permission to supply the code submitted to Sugar Calendar. Here is the DCO in detail:
```
Developer Certificate of Origin
Version 1.1

Copyright (C) 2004, 2006 The Linux Foundation and its contributors.
1 Letterman Drive
Suite D4700
San Francisco, CA, 94129

Everyone is permitted to copy and distribute verbatim copies of this
license document, but changing it is not allowed.


Developer's Certificate of Origin 1.1

By making a contribution to this project, I certify that:

(a) The contribution was created in whole or in part by me and I
    have the right to submit it under the open source license
    indicated in the file; or

(b) The contribution is based upon previous work that, to the best
    of my knowledge, is covered under an appropriate open source
    license and I have the right under that license to submit that
    work with modifications, whether created in whole or in part
    by me, under the same open source license (unless I am
    permitted to submit under a different license), as indicated
    in the file; or

(c) The contribution was provided directly to me by some other
    person who certified (a), (b) or (c) and I have not modified
    it.

(d) I understand and agree that this project and the contribution
    are public and that a record of the contribution (including all
    personal information I submit with it, including my sign-off) is
    maintained indefinitely and may be redistributed consistent with
    this project or the open source license(s) involved.
```

# Additional Resources
* [Developer's API](https://sugarcalendar.com/docs/developers/)
* [General GitHub Documentation](https://help.github.com/)
* [GitHub Pull Request documentation](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/about-pull-requests)
* [PHPUnit Tests Guide](https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html)
