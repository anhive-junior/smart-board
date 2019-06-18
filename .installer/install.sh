#!/bin/bash
COMPILED_PATH="./.compiled"

# Compile all scripts
rm -f $COMPILED_PATH || true
find ./install_stretch -maxdepth 1 -type f | sort -n | while read SCRIPT_PATH; do
	printf "# START OF ${SCRIPT_PATH}\n$(cat $SCRIPT_PATH)\n# END OF ${SCRIPT_PATH}\n" >> $COMPILED_PATH
done

# Run compiled script
bash $COMPILED_PATH
BASH_RESULT=$?

# Clear
rm -f $COMPILED_PATH || true
exit $BASH_RESULT

# END OF FILE
