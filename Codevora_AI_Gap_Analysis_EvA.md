# Codevora AI: Quotation Review and Gap Analysis vs EvA

Scope: quotation generation and PDF download for uPVC and aluminium windows and doors.
Benchmark: EvA Cloud public product description, and two public competitor listings (DoorWin, WinDoor Quote).
Limitation: EvA's own PDF samples and demo were not available to review. The comparison uses their published feature list and common industry expectations. Confirm the PDF layout against a real EvA quotation before go-live.

---

## 1. Summary

The core is strong: BOM-based costing, sized line items, discount → GST → total order, Indian amount in words, server-side quotation numbering, and tenant isolation.

This review found and fixed these defects:

| # | Defect | Impact | Status |
|---|---|---|---|
| 1 | PDF footer (page numbers, contact, powered-by) never rendered | Every PDF missing page numbers | Fixed |
| 2 | Commercial terms hardcoded as PDF fallback | Wrong terms printed for other companies | Fixed |
| 2a | Product spec claims, brand defaults and bank text hardcoded | Other companies' claims printed on item cards | Fixed (v4) |
| 3 | Terms/warranty validation always passed | Incomplete quotes could be approved | Fixed |
| 4 | Sent / Accepted / Rejected never set | Pipeline and dashboard counts were always zero | Fixed |
| 5 | Approve could revert a Sent quote to Approved | Status corruption | Fixed |
| 6 | No search or filter on quotations | Hard to find quotes at scale | Fixed |
| 7 | Footer brand "Codevora Tech" | Wrong brand on customer PDFs | Fixed |

Open items with business impact are in section 4.

---

## 2. Feature comparison

Legend: ✓ available, ◐ partial, ✗ missing.

| Capability | EvA (public claim) | Codevora AI now | Gap / note | Priority |
|---|---|---|---|---|
| Instant quotation on site | Yes | ✓ live totals while editing | Test on phone and tablet (not verified) | P1 |
| uPVC and aluminium in one app | Yes | ✓ products carry profile brand and series | None | — |
| Product configurator | 3D configurator | ◐ 2D options: design, profile, glass, hardware, mesh | No 3D viewer | P3 (large feature) |
| CAD-based output | Yes | ◐ SVG elevation per item and per size | Compare drawing quality with EvA samples | P1 |
| BOM costing and factory BOM | Costing engine | ✓ BOM, costing, Factory BOM with weight | None | — |
| Discount, freight, installation, GST | Yes | ✓ | GST fallback to 18% if company has none (see 4) | P2 |
| Amount in words | Yes | ✓ Indian format | None | — |
| Cover letter and terms | Yes | ✓ | Terms now company-owned | — |
| Quotation PDF download | Yes | ✓ download, preview, stored on private disk | Stored PDF is not regenerated when company settings change (see 4) | P1 |
| Share with customer | Not stated | ✓ WhatsApp, email draft, 30-day signed link | Email draft cannot attach PDF; use SMTP mailer | P2 |
| Quotation lifecycle | Not stated | ✓ Approved → Sent → Accepted / Rejected | Add expiry reminders | P2 |
| Customer digital acceptance | Not stated | ✗ | WinDoor Quote offers digital signing | P2 |
| CRM / leads and follow-ups | Yes ("inbuilt CRM") | ✗ customers only | Largest gap. Add leads, follow-up dates, notes | P1 |
| Quotation revisions (Rev 1, Rev 2) | Not stated | ✗ edits overwrite a draft | Keep revisions for audit | P2 |
| Export quotations to Excel | Not stated | ✗ | Useful for sales reports | P3 |
| Multi-company accounts | Not stated | ✓ Super Admin, company admins, roles | None | — |
| Product spec defaults | Per product | ◐ fallbacks in PDF (see 4) | Must come from product or company | P1 |

---

## 3. PDF download review

Checked: quotation.blade.php, layout, cover letter, item cards, financial summary, terms, bank, signature, footer.

Working: cover letter, item cards with drawings, size breakdown, financial summary, terms and prerequisites (company-owned), bank details, signature block, page footer (after fix).

Issues:
- Footer was not rendered (fixed).
- Terms fallback printed another company's commercial text (fixed).
- Escaping: terms and prerequisites go through `e()` and `nl2br`. Safe.
- Footer PHP values are written with `var_export`. Safe with PHP execution enabled.
- Stored PDF is generated on save. Changing logo, bank details, terms or GST in Company Settings does not update old PDFs until the quotation is re-saved. Needs a "PDF out of date" flag or a regenerate action (open, P1).

---

## 4. Open items (not fixed in this round)

| ID | Item | Where | Why it matters | Recommendation |
|---|---|---|---|---|
| ~~O1~~ (fixed in v4) | Product spec fallbacks are brand-specific: "CORA Hardware", "SS304 Friction Hinge Stay", "Multi-point Security Lock", "Concealed Water Drainage Caps", "10 Years Profile / 1 Year Hardware", "2 to 3 Weeks", and an inclusions list | app/DTOs/ItemDTO.php (around lines 100–130) | Another company's PDF can show these claims if its product lacks data | Move them to product or company settings. Show "—" when empty and let validation flag it |
| ~~O2~~ (fixed in v4, drafts only) | Stored PDF not refreshed after settings change | PDFService, QuotationService | Old quotes show old bank details or logo | Regenerate on demand, or flag as out of date |
| O3 | GST falls back to 18% when company has none | QuotationCalculationService | Silent tax default | Require the GST setting before saving |
| O4 | Area rounded to 3 decimals per unit before multiplying by quantity | QuotationCalculationService::calculateArea | Tiny drift on large quantities; "0% tolerance" policy | Round only the final values. Confirm against reference PDF, then update tests |
| O5 | Negative margin not flagged | QuotationValidationService | Loss-making quotes can be approved | Add a warning or block |
| O6 | Quotation edits overwrite the draft | QuotationService::updateQuotation | No history | Store revisions |
| O7 | Mobile and tablet layout of the quotation studio not tested | quotations/create.blade.php | Sales use tablets on site | Test on 768px and 390px widths |
| O8 | Laravel 9 end of life | composer.json | Security | Upgrade in a separate sprint, per the earlier plan |

---

## 5. Checks the team should run before go-live

1. `php artisan migrate` (adds `must_change_password` and `last_login_at`).
2. For existing companies, copy the template terms as described in `DEPLOY.md`.
3. Create one quotation with two sizes and a discount. Check on the PDF: page numbers, phone, "Powered by Codevora AI", totals and amount in words.
4. Try Draft → Sent. It must be refused. Approve, then Sent, then Accepted.
5. Compare one PDF with an EvA reference PDF, section by section.
6. `php artisan test`. New tests: `MultiCompanyAccessTest`, `QuotationStatusTest`.
