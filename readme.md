## Jotted WordPress Filter

Adds a shortcode to WordPress that allows you to embed a [Jotted](https://ghinda.net/jotted/) HTML / CSS editor in a page or post. 

### Why?

I'm thinking about putting together a few simple HTML tutorials on [one of my websites](https://compsci.rocks), but the idea of manually building the editor each time seems like too much work. It took about half a day to kick out a WordPress plugin that makes it way easier. 

### Installation



### Usage

You'll find a new settings page named Jotted.

**CSS Style** This will be used for the `style` attribute on the `div` that's wrapped around the Jotted editor. 

**Editor Highlighter** You can use either the Ace Editor or CodeMirror for syntax highlighting, or none if you'd rather just have generic text areas.

**Layout** Either default where each panel is its own tab or the pen layout where it looks like CodePen with the layout on top and up to 3 panels across the bottom for HTML, CSS and JavaScript.

Both CSS style and layout can be overridden using shortcode attributes. The editor cannot.

### Shortcode

Use the `[jotted]` shortcode in your posts and pages with the following attributes. They're all technically optional, but you probably at least want `html` so there's something for your visitors to play with.

For the html, css and js attributes you have a few options. The first is to base64 encode the content. This makes it easier so you don't have to worry about line breaks, tabs or HTML entities. You can also use the filename of a post attachment. For example, if you use `css="some-style.css"` as one of the attributes the plugin will look for a file named `some-style.css` attached to your post and use its contents. Or, you can just type in whatever content you want. 

**html** The HTML content to start with. This is used for the display pane and also the HTML editor pane.

**css** CSS content to start with.

**js** JavaScript content to start with.

**layout** Either `default` or `pen`

**style** `style` attribute to use on the `div` wrapper