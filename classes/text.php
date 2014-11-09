<?php

namespace Alphred\Text;


class Text {

    public function titleCase( $string ) {
        // This needs to be improved upon. Basically, it needs to account for sentence ending punctuation
        // Words that are not capitalized. Well, articles, conjunctions, and prepositions.
        $lower = [
            "the","a","an","and","but","or","for","nor","aboard","about","above","across","after","against","along",
            "amid","among","anti","around","as","at","before","behind","below","beneath","beside","besides","between",
            "beyond","but","by","concerning","considering","despite","down","during","except","excepting","excluding",
            "following","for","from","in","inside","into","like","minus","near","of","off","on","onto","opposite",
            "outside","over","past","per","plus","regarding","round","save","since","than","through","to","toward",
            "towards","under","underneath","unlike","until","up","upon","versus","via","with","within","without"
        ];

        $starting = [ '“', '‘' ]; // Add in things like upside-down exclamation points and question marks
        $stop     = [ '.', '!', '?' ];
        $words = explode( ' ', $string );
        foreach ( $words as $k => $w ) :

            // Grab the first and last characters to check for punctuation later
            $first = substr( $w, 0, 1 );
            $last = substr( $w, -1, 1 );

            // remove all punctuation (except hyphens and en- and em-dashes) from the string
            $w = preg_replace("/(?![-])\p{P}/u", "", $w);
            if ( ! in_array( $w, $lower ) || strlen( $w ) > 3 ) {
                $words[$k] = ucfirst( $w );
            } else {
                $words[$k] = lcfirst( $w );
            };

            // Add back in the punctuation if it was there.
            if ( ctype_punct( $first ) ) $words[$k] = $first . $words[$k];
            if ( ctype_punct( $last ) ) $words[$k] = $words[$k] . $last;

        endforeach;
        $words[0] = ucfirst( $words[0] );
        return implode(' ', $words );
    }

    public function camelCase( $string ) {
        // converts spaces to camelcase
        $words = explode( ' ', $string );
        foreach ( $words as $k => $w ) :
            $words[$k] = ucfirst( $w );
        endforeach;
        $words[0] = lcfirst( $words[0] );

        return implode( '', $words );
    }

    public function underscore( $string ) {
        // converts spaces to underscores
        return str_replace(' ', '_', $string );
    }

    public function hyphenate( $string ) {
        // converts spaces to hyphens
        return str_replace(' ', '-', $string );
    }

}