EmailToLink makes email addresses clickable links in a MediaWiki instance.  I created this extension with the help of Google Gemini to fulfill a simple need (more like a want) on my company's internal Wiki.  I'm sharing it in case anyone else would like to use this solution.  This does not make any database changes.  It ignores email addresses that are already contained in clickable mailto links.  It works for all existing email addresses and future ones.

Here’s a simple breakdown of how it works:

A user requests a wiki page.

MediaWiki retrieves the raw, unchanged page content (wikitext) from the database.

Just before MediaWiki converts that wikitext into the final HTML to send to the browser, this extension's hook (onInternalParseBeforeLinks) runs.

The extension takes the wikitext, finds any plain-text email addresses, and replaces them with clickable mailto: links in memory.

This modified content is then rendered as HTML and displayed to the user.

The original content in the database is never written to or altered. The conversion happens "on the fly" every single time a page is viewed. This is a major benefit because if you ever decide to disable the extension, all the email links will simply revert to being plain text on the very next page load.

If anyone would like to improve it, feel free!
