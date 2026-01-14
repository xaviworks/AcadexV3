# 🎯 Quick Start Guide - Enhanced Batch Draft System

## For Chairpersons & GE Coordinators

### 🚀 Three Ways to Create a Batch Draft

---

## Method 1: Quick Setup Wizard ⚡ (RECOMMENDED)
**Best for**: Creating new batches quickly with all options

### Steps:
1. Go to **Batch Drafts** page
2. Click **"Quick Setup Wizard"** (blue button)
3. Follow the 4-step wizard:

#### Step 1: Basic Info
- Enter batch name (or use auto-suggestion)
- Select course and year level
- Choose CO template

#### Step 2: Import Students
Choose one method:
- **📤 Upload File**: Select .xlsx, .xls, or .csv file
- **📋 Copy-Paste**: Paste directly from Excel/Google Sheets
- **🔄 Previous Batch**: Import from existing batch

#### Step 3: Select Subjects (Optional)
- Click "Load Subjects" to see available subjects
- Check subjects to attach
- Or skip and add later

#### Step 4: Review & Confirm
- Review all settings
- Check "Apply immediately" to auto-configure
- Click "Create Batch Draft"

**⏱️ Time: ~2 minutes**

---

## Method 2: Bulk Operations 🔨
**Best for**: Configuring multiple existing subjects at once

### Steps:
1. Go to **Batch Drafts** page
2. Click **"Bulk Operations"** (yellow button)
3. Use filters to find subjects:
   - Filter by course
   - Filter by year level
   - Filter by status
   - Search by name/code
4. Select batch draft to apply
5. Check subjects to configure
6. Click "Apply to Selected"

**⏱️ Time: ~30 seconds for 20 subjects**

### Dashboard Features:
- ✅ **Status Overview**: See total, configured, and pending subjects
- ✅ **Smart Filters**: Find subjects quickly
- ✅ **Bulk Actions**: Configure many subjects at once
- ✅ **Progress Tracking**: Visual progress indicators

---

## Method 3: Duplicate Existing Batch 🔄
**Best for**: New semester with similar structure

### Steps:
1. Open any existing batch draft
2. Click **"Duplicate"** button
3. Customize new batch:
   - Enter new batch name
   - Choose course and year level
   - Select CO template
4. Clone options:
   - ✅ **Clone Students**: Copy all students
   - ✅ **Auto-Promote Year Level**: Move students up one year
   - ✅ **Clone Subjects**: Match subjects by code
5. Review preview summary
6. Click "Duplicate Batch Draft"

**⏱️ Time: ~1 minute**

### Use Cases:
- 📅 New semester rollover
- 🎓 Student cohort advancement
- 📚 Course template replication

---

## 📋 Student Import Formats

### CSV/Excel Format
```
First Name, Middle Name, Last Name, Year Level
John, M, Doe, 1
Jane, A, Smith, 1
Robert, , Johnson, 1
```

### Copy-Paste Format (Tab-separated)
```
John	M	Doe	1
Jane	A	Smith	1
Robert		Johnson	1
```

### Copy-Paste Format (Comma-separated)
```
John, M, Doe, 1
Jane, A, Smith, 1
Robert, , Johnson, 1
```

**Notes**:
- ✅ Middle name is optional
- ✅ Year level is optional (uses batch year level)
- ✅ Headers are automatically detected and skipped
- ✅ Empty lines are ignored

---

## ⚡ Quick Actions

### From Index Page:
- **View Details**: Click on batch card or dropdown → View Details
- **Duplicate**: Dropdown → Duplicate
- **Edit**: Dropdown → Edit
- **Delete**: Dropdown → Delete

### From Show Page:
- **Apply Configuration**: Select subject → Click "Apply Configuration"
- **Attach Subjects**: Click "Attach Subjects" → Select subjects
- **Duplicate**: Click "Duplicate" button
- **Edit**: Click "Edit" button

---

## 🎨 Status Indicators

| Badge | Meaning |
|-------|---------|
| 🟢 **Configured** | Batch draft applied, ready for assignment |
| ⚪ **Not Configured** | Needs configuration |
| 🔵 **Assigned** | Instructor assigned |

---

## 💡 Tips & Best Practices

### Batch Names
✅ **Good**: "BSIT Y1 1st Sem 2024"
✅ **Good**: "BSBA First Year Students Fall 2024"
❌ **Bad**: "Batch1", "Test", "Students"

### When to Use Which Method:
- **First time setup**: Use **Quick Setup Wizard**
- **Multiple subjects**: Use **Bulk Operations**
- **New semester**: Use **Duplicate**
- **Single subject**: Use traditional Create form

### Workflow Recommendations:
1. Create CO Templates first (reusable)
2. Create batch draft with students
3. Attach subjects
4. Apply configuration
5. Assign to instructors

---

## ❓ Common Questions

### Q: Can I edit a batch after creation?
**A**: Yes! Click Edit on the batch draft. However, you cannot change students after they're imported. Create a new batch or duplicate instead.

### Q: What happens if I duplicate with "Auto-Promote"?
**A**: All students' year levels increase by 1. Example: Year 1 → Year 2

### Q: Can I apply configuration to the same subject multiple times?
**A**: No, configuration is applied once. If needed, delete and reapply.

### Q: How do I know if a subject is configured?
**A**: Look for the green "Configured" badge in Bulk Operations or subject lists.

### Q: Can I import students without a file?
**A**: Yes! Use the copy-paste method in the Quick Setup Wizard.

### Q: What if my Excel has different columns?
**A**: The system expects: First Name, Middle Name, Last Name, Year Level. Reorder your columns or use paste method which is more flexible.

---

## 🆘 Troubleshooting

### Issue: "Batch name already exists"
**Solution**: Choose a unique name or include semester/year in the name

### Issue: "No subjects found"
**Solution**: Check if subjects exist for the selected course and year level

### Issue: Copy-paste not working
**Solution**: 
- Ensure data has at least First Name and Last Name
- Try using tab-separated format
- Check for hidden characters

### Issue: Apply configuration failed
**Solution**: 
- Check if CO template is valid
- Ensure students are imported
- Check subject is not already configured

---

## 📞 Need Help?

If you encounter issues:
1. Check error messages (they contain helpful hints)
2. Verify your data format
3. Try the Quick Setup Wizard instead
4. Contact system administrator

---

**Last Updated**: January 13, 2026
**System Version**: Laravel 12 (AcadexV3)
