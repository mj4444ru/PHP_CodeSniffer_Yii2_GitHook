<?php

class PSR2Ext_Sniffs_Namespaces_NamespaceDeclarationSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_NAMESPACE);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in
     *                                        the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        for ($i = ($stackPtr - 1); $i >= 0; $i--) {
            if ($tokens[$i]['line'] !== $tokens[$stackPtr]['line']) {
                break;
            }
        }

        // The $i var now points to the first token on the line before the
        // namespace declaration, which must be a blank line.
        $prev = $phpcsFile->findPrevious(T_WHITESPACE, $i, 0, true);
        if ($prev === false) {
            return;
        }

        $diff = $tokens[$i]['line'] - $tokens[$prev]['line'];
        if ($diff === 1) {
            return;
        }

        if ($diff < 0) {
            $diff = 0;
        }
        $error = 'There must be one blank line before the namespace declaration';
        $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'BlankLineBefore');

        if ($fix === true) {
            if ($diff === 0) {
                $phpcsFile->fixer->addNewline($i);
            } else {
                $phpcsFile->fixer->beginChangeset();
                for ($x = $i; $x > $prev; $x--) {
                    if ($tokens[$x]['line'] === $tokens[$prev]['line']) {
                        break;
                    }
                    $phpcsFile->fixer->replaceToken($x, '');
                }
                $phpcsFile->fixer->addNewline($i);
                $phpcsFile->fixer->endChangeset();
            }
        }

    }//end process()


}//end class
