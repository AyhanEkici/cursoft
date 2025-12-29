# 🔑 Using Your New Token

## ✅ If You Already Have the Token

### Push Now:

Run this in PowerShell:
```powershell
git push -u origin main
```

**When prompted:**
- Username: `AyhanEkici`
- Password: **Paste your new token**

## 🔧 If You Need to Create Token

### Step 1: Create Token
1. Go to: https://github.com/settings/tokens
2. Click: "Generate new token" → "Generate new token (classic)"
3. **Name:** `cursoft-push`
4. **Expiration:** 90 days (or your choice)
5. **Scopes:** Check ✅ **repo** (Full control of private repositories)
6. Click: "Generate token"
7. **COPY THE TOKEN IMMEDIATELY** (you won't see it again!)

### Step 2: Push
```powershell
git push -u origin main
```
- Username: `AyhanEkici`
- Password: **Paste your token**

## 🚀 Alternative: Push with Token in URL

If you want to avoid entering credentials:

```powershell
git push https://YOUR_TOKEN@github.com/AyhanEkici/cursoft.git main
```

Replace `YOUR_TOKEN` with your actual token.

## ✅ After Push

1. Check: https://github.com/AyhanEkici/cursoft
   - Should see all your files!

2. Render will auto-deploy (if connected)

---

**Ready? Run `git push -u origin main` and paste your token when asked!**

