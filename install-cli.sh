#!/bin/bash

# ACADEX CLI Installer
# Automatically installs the acadex command for global use

echo "======================================"
echo "  ACADEX CLI Installer"
echo "======================================"
echo ""

# Get the absolute path to the acadex script
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ACADEX_PATH="$SCRIPT_DIR/acadex"

# Make sure acadex script is executable
chmod +x "$ACADEX_PATH"

echo "Project location: $SCRIPT_DIR"
echo ""

# Detect shell
if [ -n "$ZSH_VERSION" ]; then
    SHELL_NAME="zsh"
    CONFIG_FILE="$HOME/.zshrc"
elif [ -n "$BASH_VERSION" ]; then
    SHELL_NAME="bash"
    CONFIG_FILE="$HOME/.bashrc"
else
    # Default to bash
    SHELL_NAME="bash"
    CONFIG_FILE="$HOME/.bashrc"
fi

echo "Detected shell: $SHELL_NAME"
echo "Config file: $CONFIG_FILE"
echo ""

# Check if alias already exists
if grep -q "alias acadex=" "$CONFIG_FILE" 2>/dev/null; then
    echo "acadex alias already exists in $CONFIG_FILE"
    echo ""
    read -p "Do you want to update it? (y/n) " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Installation cancelled"
        exit 0
    fi
    
    # Remove old alias
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS
        sed -i '' '/alias acadex=/d' "$CONFIG_FILE"
    else
        # Linux
        sed -i '/alias acadex=/d' "$CONFIG_FILE"
    fi
    echo "Removed old alias"
fi

# Add new alias
echo "" >> "$CONFIG_FILE"
echo "# ACADEX CLI" >> "$CONFIG_FILE"
echo "alias acadex=\"$ACADEX_PATH\"" >> "$CONFIG_FILE"

echo "Added acadex alias to $CONFIG_FILE"
echo ""

# Instructions to activate
echo "======================================"
echo "  Installation Complete!"
echo "======================================"
echo ""
echo "To start using the acadex command, run:"
echo ""

if [ "$SHELL_NAME" = "zsh" ]; then
    echo "    source ~/.zshrc"
else
    echo "    source ~/.bashrc"
fi

echo ""
echo "Or simply open a new terminal window."
echo ""
echo "Then test it with:"
echo ""
echo "    acadex check"
echo ""
echo "======================================"
