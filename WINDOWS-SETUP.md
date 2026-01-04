# ACADEX CLI Setup for Windows

## Quick Start

### Prerequisites
Before using the ACADEX CLI on Windows, you need **Git for Windows** installed (includes Git Bash).

**Download Git for Windows:** https://git-scm.com/download/win

---

## Installation Methods

### Method 1: Automated Installer (Easiest) ⭐

1. **Open PowerShell** in the project folder:
   ```powershell
   cd C:\path\to\AcadexV3
   ```

2. **Run the installer:**
   ```powershell
   .\install-cli.bat
   ```

3. **Close and reopen PowerShell**

4. **Test it:**
   ```powershell
   acadex check
   ```

✅ Done!

---

### Method 2: Direct Usage (No Installation)

If you don't want to install globally, use the CLI directly:

**Option A: Using Git Bash**
```bash
cd /c/path/to/AcadexV3
./acadex check
./acadex setup
./acadex dev
```

**Option B: Using PowerShell**
```powershell
cd C:\path\to\AcadexV3
bash acadex check
bash acadex setup
bash acadex dev
```

---

## Troubleshooting

### "acadex is not recognized"

**Problem:** You see this error:
```
acadex : The term 'acadex' is not recognized as the name of a cmdlet, function, script file, or operable program.
```

**Solutions:**

1. **Did you run the installer?**
   - Run `.\install-cli.bat`
   - **You MUST close and reopen PowerShell** after installation

2. **Still not working? Use the full command:**
   ```powershell
   bash acadex check
   ```

3. **Or run it directly:**
   ```powershell
   .\acadex check
   ```

---

### "bash is not recognized"

**Problem:** Git Bash is not installed.

**Solution:** 
1. Install Git for Windows: https://git-scm.com/download/win
2. Restart PowerShell
3. Run the installer again

---

### "Permission denied" or "Execution policy"

**Problem:** PowerShell won't run scripts.

**Solution:**
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

Then run the installer again.

---

## Verifying Installation

After installation, open a **NEW PowerShell window** and run:

```powershell
acadex check
```

You should see system requirements check output.

---

## Manual Installation

If the automated installer doesn't work, add this function manually:

1. **Find your PowerShell profile:**
   ```powershell
   $PROFILE
   ```

2. **Open it in Notepad:**
   ```powershell
   notepad $PROFILE
   ```

3. **Add this function** (replace path):
   ```powershell
   # ACADEX CLI
   function acadex {
       $acadexPath = "C:/path/to/AcadexV3/acadex"
       & bash $acadexPath @args
   }
   ```

4. **Save and restart PowerShell**

5. **Test:**
   ```powershell
   acadex check
   ```

---

## Next Steps

After installation:
- Run `acadex setup` to install dependencies
- Run `acadex dev` to start development servers
- See all commands: `acadex help`

---

## Need Help?

If you're still having issues:
1. Make sure Git for Windows is installed
2. Close ALL PowerShell windows after running the installer
3. Open a FRESH PowerShell window
4. Try running `bash acadex check` first to confirm the script works

---

**Documentation:** https://xaviworks.github.io/AcadexV3/
