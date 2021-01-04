Introduction
============

Maestro is a *package maintainence automator for PHP*. It is essentially a
parallel task runner - think of it as Ansible for packages.

Use it to automate workflows on multiple repositories at once, for example:

- Synchronize CI configuration
- Maintain sections in ``README`` files.
- Perform tagging, merging, open and close PRs.
- Run surveys (e.g. latest git tags, branch aliases, CI statuses etc)
- Perform automated upgrades
