<?php

class EmailToLinkHooks {
    /**
     * Hook function for InternalParseBeforeLinks
     *
     * This function is called after templates are expanded but before links are processed.
     * It finds email addresses in the text and converts them to mailto links,
     * while ignoring text that is already inside wikitext link brackets.
     *
     * @param Parser $parser The parser object.
     * @param string &$text The text being parsed.
     * @return bool Always true.
     */
    public static function onInternalParseBeforeLinks( Parser $parser, &$text ) {
        // Regex to split the text by MediaWiki links (external [...] and internal [[...]]).
        // We must check for [[...]] first, otherwise [.*?] would match [[link.
        // The 's' flag (DOTALL) makes '.' match newlines, in case links span lines.
        $parts = preg_split( '/(\[\[.*?\]\]|\[.*?\])/s', $text, -1, PREG_SPLIT_DELIM_CAPTURE );

        $newText = '';
        foreach ( $parts as $i => $part ) {
            // Even-indexed parts (0, 2, 4...) are text *outside* the brackets.
            // Odd-indexed parts (1, 3, 5...) are the link syntax itself (e.g., "[mailto:...]").
            if ( $i % 2 === 0 ) {
                // This is plain text, so we run our email linker on it.
                $newText .= self::findAndLinkEmails( $part );
            } else {
                // This is link text, so we add it back completely unchanged.
                $newText .= $part;
            }
        }
        $text = $newText;
        return true;
    }

    /**
     * Helper function to find and replace email addresses in a given string.
     *
     * @param string $textChunk The text chunk to process (assumed to be outside link brackets).
     * @return string The text with emails linked.
     */
    private static function findAndLinkEmails( $textChunk ) {
        // Our original "smart" regex is still useful here.
        // Even outside brackets, this prevents us from linking emails that might
        // be in other HTML attributes (e.g., from a template).
        $regex = '/(?<!href="mailto:|mailto:|=|\'|")\b([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,})\b(?!>|\'|")/i';

        return preg_replace_callback(
            $regex,
            function( $matches ) {
                // $matches[0] contains the full matched email address.
                $email = $matches[0];

                // We encode the email address to protect it slightly from spam bots.
                $encodedEmail = htmlspecialchars( $email, ENT_QUOTES );

                // Create the mailto link.
                return '<a href="mailto:' . $encodedEmail . '">' . $encodedEmail . '</a>';
            },
            $textChunk
        );
    }
}

