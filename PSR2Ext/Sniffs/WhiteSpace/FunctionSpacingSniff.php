<?php

class PSR2Ext_Sniffs_WhiteSpace_FunctionSpacingSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_FUNCTION);

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
        $this->processBefore($phpcsFile, $stackPtr);
        $this->processAfter($phpcsFile, $stackPtr);
    }//end process()


    protected function processBefore(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

//        // The $i var now points to the first token on the line after the
//        // function declaration, which must be a blank line.
//        $next = $phpcsFile->findNext(T_WHITESPACE, $i, $phpcsFile->numTokens, true);
//        if ($next === false || $tokens[$next]['code'] === T_CLOSE_CURLY_BRACKET) {
//            return;
//        }

//if ($tokens[$next]['code'] !== T_FUNCTION && $tokens[$next]['code'] !== T_CLASS && $tokens[$next]['code'] !== T_PRIVATE) {
//var_dump(array_slice($tokens, $next - 2, 5, true));die();
//}


        for ($i = ($stackPtr - 1); $i >= 0; $i--) {
            if ($tokens[$i]['line'] !== $tokens[$stackPtr]['line']) {
                break;
            }
        }

        // The $i var now points to the first token on the line before the
        // function declaration, which must be a blank line.
        $prev = $phpcsFile->findPrevious(T_WHITESPACE, $i, 0, true);
        if ($prev === false) {
            return;
        }

        if ($tokens[$prev]['code'] === T_DOC_COMMENT_CLOSE_TAG && $tokens[$prev]['line'] === $tokens[$i]['line']) {
            $comPtr = $tokens[$prev]['comment_opener'];
            for ($i = ($comPtr - 1); $i >= 0; $i--) {
                if ($tokens[$i]['line'] !== $tokens[$comPtr]['line']) {
                    break;
                }
            }
            $prev = $phpcsFile->findPrevious(T_WHITESPACE, $i, 0, true);
            if ($prev === false) {
                return;
            }
        }

        $openBracket = $tokens[$prev]['code'] === T_OPEN_CURLY_BRACKET;

//if ($tokens[$prev]['code'] !== T_SEMICOLON && $tokens[$prev]['code'] !== T_CLOSE_CURLY_BRACKET) {
//var_dump(array_slice($tokens, $prev - 2, 5, true), $openBracket);die();
//}

        $diff = $tokens[$i]['line'] - $tokens[$prev]['line'];
        if ((!$openBracket && $diff === 1) || ($openBracket && $diff === 0)) {
            return;
        }

        if ($diff < 0) {
            $diff = 0;
        }

        $error = 'There must be one blank line before the function declaration';
        $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'BlankLineBefore');

        if ($fix === true) {
            if ($diff === 0) {
                $phpcsFile->fixer->addNewline($prev);
            } else {
                $phpcsFile->fixer->beginChangeset();
                for ($x = $i; $x > $prev; $x--) {
                    if ($tokens[$x]['line'] === $tokens[$prev]['line']) {
                        break;
                    }
                    $phpcsFile->fixer->replaceToken($x, '');
                }
                if (!$openBracket) {
                    $phpcsFile->fixer->addNewline($i);
                }
                $phpcsFile->fixer->endChangeset();
            }
        }
    }//end processBefore()

    protected function processAfter(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['scope_closer']) === false) {
            $closer = $phpcsFile->findNext(T_SEMICOLON, $stackPtr);
        } else {
            $closer = $tokens[$stackPtr]['scope_closer'];
        }

        for ($i = ($closer + 1); $i < ($phpcsFile->numTokens - 1); $i++) {
            if ($tokens[$i]['line'] !== $tokens[$closer]['line']) {
                break;
            }
        }

        // The $i var now points to the first token on the line after the
        // function declaration, which must be a blank line.
        $next = $phpcsFile->findNext(T_WHITESPACE, $i, $phpcsFile->numTokens, true);
        if ($next === false || $tokens[$next]['code'] === T_CLOSE_CURLY_BRACKET) {
            return;
        }

        $diff = ($tokens[$next]['line'] - $tokens[$i]['line']);
        if ($diff === 1) {
            return;
        }

        if ($diff < 0) {
            $diff = 0;
        }

        $error = 'There must be one blank line after the function declaration';
        $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'BlankLineAfter');

        if ($fix === true) {
            if ($diff === 0) {
                $phpcsFile->fixer->addNewlineBefore($i);
            } else {
                $phpcsFile->fixer->beginChangeset();
                for ($x = $i; $x < $next; $x++) {
                    if ($tokens[$x]['line'] === $tokens[$next]['line']) {
                        break;
                    }
                    $phpcsFile->fixer->replaceToken($x, '');
                }
                $phpcsFile->fixer->addNewline($i);
                $phpcsFile->fixer->endChangeset();
            }
        }
    }//end processAfter()


}//end class
