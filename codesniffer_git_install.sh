#!/bin/sh

ABSOLUTE_FILENAME=`readlink -e "$0"`
SOURCE_PATH=`dirname "$ABSOLUTE_FILENAME"`
VENDOR_PATH="$SOURCE_PATH";
VENDOR_PATH=`dirname "$VENDOR_PATH"`
VENDOR_PATH=`dirname "$VENDOR_PATH"`

PROJECT_PATH=`dirname "$VENDOR_PATH"`;
if [ ! -d "$PROJECT_PATH/.git/hooks/" ]; then
    PROJECT_PATH=`dirname "$PROJECT_PATH"`;
    if [ ! -d "$PROJECT_PATH/.git/hooks/" ]; then
        PROJECT_PATH=`dirname "$PROJECT_PATH"`;
        if [ ! -d "$PROJECT_PATH/.git/hooks/" ]; then
            PROJECT_PATH=`dirname "$PROJECT_PATH"`;
            if [ ! -d "$PROJECT_PATH/.git/hooks/" ]; then
                PROJECT_PATH=`dirname "$PROJECT_PATH"`;
                if [ ! -d "$PROJECT_PATH/.git/hooks/" ]; then
                    echo "Unable to install hook: Git folder not found!"
                    exit 1
                fi
            fi
        fi
    fi
fi
HOOKS_PATH="$PROJECT_PATH/.git/hooks"
SHORT_VENDOR_PATH="${VENDOR_PATH#$PROJECT_PATH}"

echo "Install hook in \"$HOOKS_PATH\""
if [ -e "${HOOKS_PATH}/pre-commit" ]; then
    if [ -f "${HOOKS_PATH}/pre-commit" ]; then
        echo "Remove file \"${HOOKS_PATH}/pre-commit\""
        rm -f ${HOOKS_PATH}/pre-commit
        if [ -e "${GIT_PATH}pre-commit" ]; then
            echo "Unable to install hook: \"${HOOKS_PATH}/pre-commit\" is readonly!";
            exit 1
        fi
    else
        echo "Unable to install hook: \"${HOOKS_PATH}/pre-commit\" is not file!";
        exit 1
    fi
fi

echo "Copy hook from \"$SOURCE_PATH/hooks/pre-commit\" to \"${HOOKS_PATH}/pre-commit\"";
cp $SOURCE_PATH/hooks/pre-commit ${HOOKS_PATH}/pre-commit
chmod +x ${HOOKS_PATH}/pre-commit
sed 's~VENDOR="$PROJECT[^"]*"~VENDOR="$PROJECT'$SHORT_VENDOR_PATH'"~' -i ${HOOKS_PATH}/pre-commit
