# Frontend

`Site\Model\ItemModel` extends the backend `ItemModel`, adding consent/CAPTCHA validation,
Akismet checks, and email sending.

## Submission Flow

1. User loads form → `Site\Controller\ItemController` → `Site\View\Item\HtmlView`
2. User submits → `Site\Model\ItemModel::save()` validates consent, checks CAPTCHA, calls parent
   save, runs Akismet spam check, emails administrators (if not spam), sends auto-reply (if
   configured), redirects to `ThanksController`
