<comment>
 Commands (lambo COMMANDNAME):</comment>
 <info>help</info>                          Display this screen
 <info>edit-config</info>                   Edit "~/.lambo/config" file
 <info>edit-after </info>                   Edit "~/.lambo/after" file
 <info>new</info>                           Scaffold a new Laravel application

 <comment>Usage:</comment>
 <info>lambo help</info>
 <info>lambo</info> [common options] <info>edit-config</info> [--editor=<editor>]
 <info>lambo</info> [common options] <info>edit-after</info> [--editor=<editor>]
 <info>lambo</info> [common options] <info>new myApplication</info> [--editor=<editor>] [options]

 <comment>Options (lambo new myApplication):</comment>
{!! $lamboNewOptions !!}
 <comment>Common options:</comment>
{!! $commonOptions !!}
