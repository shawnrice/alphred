You can configure how Alphred behaves by adding a `workflow.ini` file next to your `info.plist` file. In the near future (dependent on development time), Packal will start to use `workflow.ini` files to make workflow submission easier.

You can find an example `workflow.ini` file in the [Github Repo](https://github.com/shawnrice/alphred/blob/master/example/workflow.ini).

One benefit of using the `workflow.ini` file is that you can set certain variables outside of the Alphred codebase, making function calls shorter (for using the config wrapper), and debug values for your users to change when it comes to logging.