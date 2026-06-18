"""
generate_word.py
Generates a professional Word document from FINAL_PROJECT_DOCUMENTATION_UPDATED.md
Changes:
  - Large professional fonts (body 14pt, headings up to 28pt)
  - Real Word TOC with page numbers (update with Ctrl+A -> F9 in Word)
  - Each Chapter (## Chapter N) starts on a new page
  - Horizontal rules removed (replaced with spacing)
  - Extra --- lines skipped
"""

from docx import Document
from docx.shared import Pt, RGBColor, Inches, Cm
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml.ns import qn
from docx.oxml import OxmlElement
import re, os

# ═══════════════════════════════════════════════
#  FONT SIZES
# ═══════════════════════════════════════════════
FONT_NORMAL        = Pt(14)
FONT_H1            = Pt(28)
FONT_H2            = Pt(22)
FONT_H3            = Pt(18)
FONT_H4            = Pt(15)
FONT_LIST          = Pt(14)
FONT_TABLE_HDR     = Pt(12)
FONT_TABLE_BODY    = Pt(12)
FONT_CODE          = Pt(11)
FONT_SUMMARY_LABEL = Pt(13)
FONT_SUMMARY_BODY  = Pt(13)
FONT_COVER_TITLE   = Pt(28)
FONT_COVER_SUB     = Pt(16)
FONT_COVER_AUTHOR  = Pt(13)
FONT_COVER_INFO    = Pt(13)
FONT_FOOTER        = Pt(10)
FONT_BLOCKQUOTE    = Pt(13)
FONT_INLINE_CODE   = Pt(12)
FONT_TOC_TITLE     = Pt(22)
FONT_TOC_ENTRY     = Pt(13)


# ═══════════════════════════════════════════════
#  HELPERS
# ═══════════════════════════════════════════════

def rgb(r, g, b):
    return RGBColor(r, g, b)

GREEN_DARK   = rgb(0x1B, 0x5E, 0x20)
GREEN_MID    = rgb(0x2E, 0x7D, 0x32)
GREEN_LIGHT  = rgb(0x33, 0x69, 0x1E)
WHITE        = rgb(0xFF, 0xFF, 0xFF)
GRAY         = rgb(0x55, 0x55, 0x55)
CODE_RED     = rgb(0xC7, 0x25, 0x4E)


def set_cell_bg(cell, hex_color):
    tc   = cell._tc
    tcPr = tc.get_or_add_tcPr()
    shd  = OxmlElement('w:shd')
    shd.set(qn('w:val'),   'clear')
    shd.set(qn('w:color'), 'auto')
    shd.set(qn('w:fill'),  hex_color)
    tcPr.append(shd)


def para_shading(paragraph, hex_color):
    pPr = paragraph._p.get_or_add_pPr()
    shd = OxmlElement('w:shd')
    shd.set(qn('w:val'),   'clear')
    shd.set(qn('w:color'), 'auto')
    shd.set(qn('w:fill'),  hex_color)
    pPr.append(shd)


def left_border(paragraph, color='2E7D32', sz='14', space='10'):
    pPr  = paragraph._p.get_or_add_pPr()
    pBdr = OxmlElement('w:pBdr')
    left = OxmlElement('w:left')
    left.set(qn('w:val'),   'single')
    left.set(qn('w:sz'),    sz)
    left.set(qn('w:space'), space)
    left.set(qn('w:color'), color)
    pBdr.append(left)
    pPr.append(pBdr)


def parse_inline(paragraph, text):
    """Bold, italic, inline-code markdown → Word runs."""
    pat  = re.compile(r'(\*\*(.+?)\*\*|\*(.+?)\*|`(.+?)`)')
    last = 0
    for m in pat.finditer(text):
        if m.start() > last:
            paragraph.add_run(text[last:m.start()])
        if m.group(2):
            r = paragraph.add_run(m.group(2));  r.bold = True
        elif m.group(3):
            r = paragraph.add_run(m.group(3));  r.italic = True
        elif m.group(4):
            r = paragraph.add_run(m.group(4))
            r.font.name  = 'Courier New'
            r.font.size  = FONT_INLINE_CODE
            r.font.color.rgb = CODE_RED
        last = m.end()
    if last < len(text):
        paragraph.add_run(text[last:])


def apply_font(para, size, name='Calibri', color=None):
    for run in para.runs:
        run.font.name = name
        run.font.size = size
        if color:
            run.font.color.rgb = color


# ═══════════════════════════════════════════════
#  STYLE SETUP
# ═══════════════════════════════════════════════

def setup_styles(doc):
    s = doc.styles

    n = s['Normal']
    n.font.name = 'Calibri'
    n.font.size = FONT_NORMAL
    n.paragraph_format.space_after  = Pt(10)
    n.paragraph_format.line_spacing = Pt(22)

    for lvl, size, color, before, after in [
        (1, FONT_H1, GREEN_DARK,  Pt(30), Pt(16)),
        (2, FONT_H2, GREEN_MID,   Pt(24), Pt(12)),
        (3, FONT_H3, GREEN_LIGHT, Pt(18), Pt(10)),
    ]:
        h = s[f'Heading {lvl}']
        h.font.name  = 'Calibri'
        h.font.size  = size
        h.font.bold  = True
        h.font.color.rgb = color
        h.paragraph_format.space_before = before
        h.paragraph_format.space_after  = after

    for style_name, size in [('List Bullet', FONT_LIST), ('List Number', FONT_LIST)]:
        st = s[style_name]
        st.font.name  = 'Calibri'
        st.font.size  = size
        st.paragraph_format.left_indent  = Inches(0.4)
        st.paragraph_format.space_after  = Pt(6)


# ═══════════════════════════════════════════════
#  COVER PAGE
# ═══════════════════════════════════════════════

def add_cover_page(doc):
    # Top green strip
    strip = doc.add_paragraph()
    strip.paragraph_format.space_before = Pt(20)
    strip.paragraph_format.space_after  = Pt(0)
    strip.alignment = WD_ALIGN_PARAGRAPH.CENTER
    para_shading(strip, '1B5E20')
    strip.add_run(' ' * 5).font.size = Pt(8)

    # University name
    uni = doc.add_paragraph()
    uni.alignment = WD_ALIGN_PARAGRAPH.CENTER
    uni.paragraph_format.space_before = Pt(20)
    uni.paragraph_format.space_after  = Pt(4)
    r = uni.add_run('Government College University Faisalabad')
    r.font.name = 'Calibri'; r.font.size = Pt(15)
    r.font.color.rgb = GREEN_MID

    dept = doc.add_paragraph()
    dept.alignment = WD_ALIGN_PARAGRAPH.CENTER
    dept.paragraph_format.space_after = Pt(24)
    r2 = dept.add_run('Department of Software Engineering')
    r2.font.name = 'Calibri'; r2.font.size = Pt(13); r2.font.italic = True
    r2.font.color.rgb = GREEN_LIGHT

    # Main title
    tp = doc.add_paragraph()
    tp.alignment = WD_ALIGN_PARAGRAPH.CENTER
    tp.paragraph_format.space_before = Pt(10)
    tp.paragraph_format.space_after  = Pt(10)
    tr = tp.add_run(
        'Manual to Digital Agriculture\nFertilizer & Spray\nInventory Management System'
    )
    tr.font.name = 'Calibri'; tr.font.size = FONT_COVER_TITLE
    tr.font.bold = True;  tr.font.color.rgb = GREEN_DARK

    # Subtitle
    sp = doc.add_paragraph()
    sp.alignment = WD_ALIGN_PARAGRAPH.CENTER
    sp.paragraph_format.space_after = Pt(30)
    sr = sp.add_run(
        'With Integrated Barcode Scanning and Facial Attendance System'
    )
    sr.font.name = 'Calibri'; sr.font.size = FONT_COVER_SUB
    sr.font.italic = True; sr.font.color.rgb = GREEN_MID

    # Divider line (thin paragraph border)
    div = doc.add_paragraph()
    div.paragraph_format.space_before = Pt(2)
    div.paragraph_format.space_after  = Pt(20)
    pPr  = div._p.get_or_add_pPr()
    pBdr = OxmlElement('w:pBdr')
    bot  = OxmlElement('w:bottom')
    bot.set(qn('w:val'), 'single'); bot.set(qn('w:sz'), '8')
    bot.set(qn('w:space'), '1');    bot.set(qn('w:color'), '2E7D32')
    pBdr.append(bot); pPr.append(pBdr)

    # Author table
    tbl = doc.add_table(rows=1, cols=2)
    tbl.style = 'Table Grid'
    for idx, (name, reg) in enumerate([
        ('Abdullah Mehboob',  '222030  |  Reg: 2022-GCUF-02608'),
        ('Muzammal Ali',      '222025  |  Reg: 2022-GCUF-02603'),
    ]):
        cell = tbl.cell(0, idx)
        set_cell_bg(cell, 'E8F5E9')
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        r1 = p.add_run(name + '\n')
        r1.font.name = 'Calibri'; r1.font.size = FONT_COVER_AUTHOR + Pt(1)
        r1.font.bold = True; r1.font.color.rgb = GREEN_DARK
        r2 = p.add_run(reg)
        r2.font.name = 'Calibri'; r2.font.size = FONT_COVER_AUTHOR
        r2.font.color.rgb = GREEN_MID

    doc.add_paragraph().paragraph_format.space_after = Pt(14)

    # Info rows
    for label, value in [
        ('Degree:',      'Bachelor of Science in Software Engineering'),
        ('Supervisor:',  'Dr. Awais — Dept. of Software Engineering, GCUF'),
        ('Session:',     '2022 – 2026'),
    ]:
        p = doc.add_paragraph()
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p.paragraph_format.space_after = Pt(5)
        rl = p.add_run(label + '  ')
        rl.font.name = 'Calibri'; rl.font.size = FONT_COVER_INFO
        rl.font.bold = True; rl.font.color.rgb = GREEN_MID
        rv = p.add_run(value)
        rv.font.name = 'Calibri'; rv.font.size = FONT_COVER_INFO

    doc.add_page_break()


# ═══════════════════════════════════════════════
#  TABLE OF CONTENTS  (Word auto-field)
# ═══════════════════════════════════════════════

def add_toc(doc):
    # TOC heading
    tp = doc.add_paragraph()
    tp.alignment = WD_ALIGN_PARAGRAPH.CENTER
    tp.paragraph_format.space_before = Pt(0)
    tp.paragraph_format.space_after  = Pt(18)
    r = tp.add_run('TABLE OF CONTENTS')
    r.font.name = 'Calibri'; r.font.size = FONT_TOC_TITLE
    r.font.bold = True; r.font.color.rgb = GREEN_DARK

    # TOC field — Word populates this automatically
    # Press Ctrl+A then F9 in Word to update page numbers
    toc_para = doc.add_paragraph()
    toc_run  = toc_para.add_run()

    fldChar_begin = OxmlElement('w:fldChar')
    fldChar_begin.set(qn('w:fldCharType'), 'begin')
    toc_run._r.append(fldChar_begin)

    instr = OxmlElement('w:instrText')
    instr.set(qn('xml:space'), 'preserve')
    instr.text = ' TOC \\o "1-3" \\h \\z \\u '
    toc_run._r.append(instr)

    fldChar_sep = OxmlElement('w:fldChar')
    fldChar_sep.set(qn('w:fldCharType'), 'separate')
    toc_run._r.append(fldChar_sep)

    # Placeholder lines (Word replaces these on update)
    placeholder_entries = [
        'Chapter 1 — Introduction & Motivation',
        'Chapter 2 — System Scope & Requirements',
        'Chapter 3 — System Architecture & Database Design',
        'Chapter 4 — Implementation Details',
        'Chapter 5 — Testing, Deployment & User Guide',
        'Chapter 6 — Conclusion & Future Work',
    ]
    for entry in placeholder_entries:
        ep = doc.add_paragraph()
        ep.paragraph_format.left_indent  = Inches(0.2)
        ep.paragraph_format.space_after  = Pt(5)
        er = ep.add_run(entry)
        er.font.name = 'Calibri'
        er.font.size = FONT_TOC_ENTRY
        er.font.color.rgb = GREEN_MID

    toc_end_run  = toc_para.add_run()
    fldChar_end  = OxmlElement('w:fldChar')
    fldChar_end.set(qn('w:fldCharType'), 'end')
    toc_end_run._r.append(fldChar_end)

    # Note to user
    note = doc.add_paragraph()
    note.paragraph_format.space_before = Pt(10)
    note.paragraph_format.left_indent  = Inches(0.2)
    nr = note.add_run(
        'Note: To update page numbers, open in Microsoft Word, press Ctrl+A, then press F9.'
    )
    nr.font.name = 'Calibri'; nr.font.size = Pt(10)
    nr.font.italic = True; nr.font.color.rgb = GRAY

    doc.add_page_break()


# ═══════════════════════════════════════════════
#  MODULE SUMMARY BOX
# ═══════════════════════════════════════════════

def add_module_summary(doc, text):
    clean = re.sub(r'\*\*What this chapter is about:\*\*', '', text).strip()
    p = doc.add_paragraph()
    p.paragraph_format.left_indent  = Inches(0.35)
    p.paragraph_format.right_indent = Inches(0.35)
    p.paragraph_format.space_before = Pt(6)
    p.paragraph_format.space_after  = Pt(16)
    left_border(p, color='2E7D32', sz='14', space='10')
    para_shading(p, 'E8F5E9')

    lb = p.add_run('Module Summary:  ')
    lb.font.name = 'Calibri'; lb.font.size = FONT_SUMMARY_LABEL
    lb.font.bold = True; lb.font.italic = True
    lb.font.color.rgb = GREEN_DARK

    br = p.add_run(clean)
    br.font.name = 'Calibri'; br.font.size = FONT_SUMMARY_BODY
    br.font.italic = True; br.font.color.rgb = rgb(0x33, 0x33, 0x33)


# ═══════════════════════════════════════════════
#  CODE BLOCK
# ═══════════════════════════════════════════════

def add_code_block(doc, lines):
    if not lines:
        return
    # Top padding
    top = doc.add_paragraph()
    top.paragraph_format.space_before = Pt(0)
    top.paragraph_format.space_after  = Pt(0)

    for line in lines:
        p = doc.add_paragraph()
        p.paragraph_format.left_indent  = Inches(0.3)
        p.paragraph_format.right_indent = Inches(0.3)
        p.paragraph_format.space_before = Pt(0)
        p.paragraph_format.space_after  = Pt(0)
        para_shading(p, 'F3F3F3')
        r = p.add_run(line if line.strip() else ' ')
        r.font.name  = 'Courier New'
        r.font.size  = FONT_CODE
        r.font.color.rgb = rgb(0x1A, 0x1A, 0x1A)

    bot = doc.add_paragraph()
    bot.paragraph_format.space_after = Pt(12)


# ═══════════════════════════════════════════════
#  TABLE
# ═══════════════════════════════════════════════

def add_md_table(doc, header_row, data_rows):
    n_cols = len(header_row)
    n_rows = 1 + len(data_rows)
    tbl = doc.add_table(rows=n_rows, cols=n_cols)
    tbl.style = 'Table Grid'

    # Header
    hcells = tbl.rows[0].cells
    for i, h in enumerate(header_row):
        set_cell_bg(hcells[i], '1B5E20')
        hp = hcells[i].paragraphs[0]
        hp.alignment = WD_ALIGN_PARAGRAPH.CENTER
        hr = hp.add_run(h.strip())
        hr.font.name = 'Calibri'; hr.font.size = FONT_TABLE_HDR
        hr.font.bold = True; hr.font.color.rgb = WHITE

    # Data rows
    for ri, row in enumerate(data_rows):
        fill  = 'EDF7ED' if ri % 2 == 0 else 'FFFFFF'
        cells = tbl.rows[ri + 1].cells
        for ci, ct in enumerate(row):
            set_cell_bg(cells[ci], fill)
            cp = cells[ci].paragraphs[0]
            cp.alignment = WD_ALIGN_PARAGRAPH.LEFT
            parse_inline(cp, ct.strip())
            for run in cp.runs:
                run.font.name = 'Calibri'
                run.font.size = FONT_TABLE_BODY

    doc.add_paragraph().paragraph_format.space_after = Pt(10)


# ═══════════════════════════════════════════════
#  FOOTER
# ═══════════════════════════════════════════════

def add_footer(doc):
    for section in doc.sections:
        footer = section.footer
        fp = footer.paragraphs[0]
        fp.clear()
        fp.alignment = WD_ALIGN_PARAGRAPH.CENTER

        r1 = fp.add_run('Agriculture Tracker  —  Final Year Project Documentation     |     Page ')
        r1.font.name = 'Calibri'; r1.font.size = FONT_FOOTER
        r1.font.color.rgb = GRAY

        r2 = fp.add_run()
        r2.font.name = 'Calibri'; r2.font.size = FONT_FOOTER
        r2.font.color.rgb = GRAY
        fB = OxmlElement('w:fldChar'); fB.set(qn('w:fldCharType'), 'begin')
        it = OxmlElement('w:instrText'); it.text = 'PAGE'
        fE = OxmlElement('w:fldChar'); fE.set(qn('w:fldCharType'), 'end')
        r2._r.append(fB); r2._r.append(it); r2._r.append(fE)

        r3 = fp.add_run('  of  ')
        r3.font.name = 'Calibri'; r3.font.size = FONT_FOOTER; r3.font.color.rgb = GRAY

        r4 = fp.add_run()
        r4.font.name = 'Calibri'; r4.font.size = FONT_FOOTER; r4.font.color.rgb = GRAY
        fB2 = OxmlElement('w:fldChar'); fB2.set(qn('w:fldCharType'), 'begin')
        it2 = OxmlElement('w:instrText'); it2.text = 'NUMPAGES'
        fE2 = OxmlElement('w:fldChar'); fE2.set(qn('w:fldCharType'), 'end')
        r4._r.append(fB2); r4._r.append(it2); r4._r.append(fE2)


# ═══════════════════════════════════════════════
#  MAIN BUILD
# ═══════════════════════════════════════════════

def build_document(md_path, out_path):
    doc = Document()

    for section in doc.sections:
        section.top_margin    = Cm(2.5)
        section.bottom_margin = Cm(2.5)
        section.left_margin   = Cm(3.0)
        section.right_margin  = Cm(2.5)

    setup_styles(doc)
    add_cover_page(doc)
    add_toc(doc)

    with open(md_path, 'r', encoding='utf-8') as f:
        raw_lines = f.readlines()

    # Strip the markdown TOC section entirely
    lines = []
    skip_toc = False
    for ln in raw_lines:
        s = ln.strip()
        if s == '## Table of Contents':
            skip_toc = True
            continue
        if skip_toc:
            # TOC entries look like "N. [text](#anchor)"
            if re.match(r'^\d+\.\s+\[', s) or s == '':
                continue
            else:
                skip_toc = False
        lines.append(ln)

    i        = 0
    in_code  = False
    code_acc = []

    while i < len(lines):
        raw      = lines[i].rstrip('\n')
        stripped = raw.strip()

        # ── CODE BLOCK ─────────────────────────────────────────
        if stripped.startswith('```'):
            if not in_code:
                in_code = True;  code_acc = []
            else:
                in_code = False
                add_code_block(doc, code_acc)
                code_acc = []
            i += 1;  continue

        if in_code:
            code_acc.append(raw);  i += 1;  continue

        # ── HORIZONTAL RULE — skip it (use spacing instead) ────
        if stripped == '---':
            # Only add a little space, no visible line
            sp = doc.add_paragraph()
            sp.paragraph_format.space_before = Pt(2)
            sp.paragraph_format.space_after  = Pt(2)
            i += 1;  continue

        # ── TABLE ───────────────────────────────────────────────
        if stripped.startswith('|'):
            tbl_lines = []
            while i < len(lines) and lines[i].strip().startswith('|'):
                tbl_lines.append(lines[i].strip())
                i += 1
            real = [l for l in tbl_lines if not re.match(r'^[\|\-\s:]+$', l)]
            if real:
                parsed = [[c for c in r.split('|') if c.strip()] for r in real]
                if len(parsed) >= 1:
                    add_md_table(doc, parsed[0], parsed[1:])
            continue

        # ── HEADING 1 ───────────────────────────────────────────
        if re.match(r'^# [^#]', stripped):
            doc.add_heading(stripped[2:].strip(), level=1)
            i += 1;  continue

        # ── HEADING 2  — each Chapter starts on new page ────────
        if re.match(r'^## [^#]', stripped):
            text = stripped[3:].strip()
            # New page for every Chapter heading
            if re.match(r'^Chapter\s+\d', text):
                doc.add_page_break()
            doc.add_heading(text, level=2)
            i += 1;  continue

        # ── HEADING 3  — check for Module Summary ───────────────
        if re.match(r'^### ', stripped):
            text = stripped[4:].strip()
            if text == 'Module Summary':
                i += 1
                summary = ''
                while i < len(lines):
                    nl = lines[i].strip()
                    if nl.startswith('>'):
                        summary += nl[1:].strip() + ' '
                        i += 1
                    elif nl == '':
                        i += 1;  break
                    else:
                        break
                add_module_summary(doc, summary.strip())
                continue
            doc.add_heading(text, level=3)
            i += 1;  continue

        # ── HEADING 4 ───────────────────────────────────────────
        if re.match(r'^#### ', stripped):
            p = doc.add_paragraph()
            p.paragraph_format.space_before = Pt(14)
            p.paragraph_format.space_after  = Pt(6)
            r = p.add_run(stripped[5:].strip())
            r.font.name = 'Calibri'; r.font.size = FONT_H4
            r.font.bold = True; r.font.color.rgb = GREEN_LIGHT
            i += 1;  continue

        # ── BLOCKQUOTE (standalone) ──────────────────────────────
        if stripped.startswith('>'):
            p = doc.add_paragraph()
            p.paragraph_format.left_indent  = Inches(0.4)
            p.paragraph_format.space_before = Pt(4)
            p.paragraph_format.space_after  = Pt(10)
            left_border(p, color='81C784', sz='8', space='8')
            parse_inline(p, stripped[1:].strip())
            for run in p.runs:
                run.font.name = 'Calibri';  run.font.size = FONT_BLOCKQUOTE
                run.font.italic = True
            i += 1;  continue

        # ── NUMBERED LIST ────────────────────────────────────────
        if re.match(r'^\d+\. ', stripped):
            p = doc.add_paragraph(style='List Number')
            parse_inline(p, re.sub(r'^\d+\. ', '', stripped))
            for run in p.runs:
                run.font.name = 'Calibri'; run.font.size = FONT_LIST
            i += 1;  continue

        # ── BULLET LIST ──────────────────────────────────────────
        if re.match(r'^[-*] ', stripped):
            indent_lvl = (len(raw) - len(raw.lstrip())) // 3
            p = doc.add_paragraph(style='List Bullet')
            if indent_lvl > 0:
                p.paragraph_format.left_indent = Inches(0.4 + 0.25 * indent_lvl)
            parse_inline(p, stripped[2:])
            for run in p.runs:
                run.font.name = 'Calibri'; run.font.size = FONT_LIST
            i += 1;  continue

        # ── EMPTY LINE ───────────────────────────────────────────
        if stripped == '':
            i += 1;  continue

        # ── REGULAR PARAGRAPH ────────────────────────────────────
        p = doc.add_paragraph()
        p.paragraph_format.space_after  = Pt(10)
        p.paragraph_format.line_spacing = Pt(22)
        parse_inline(p, stripped)
        for run in p.runs:
            run.font.name = 'Calibri'
            if not run.font.size:
                run.font.size = FONT_NORMAL
        i += 1

    add_footer(doc)
    doc.save(out_path)
    print(f"Word document saved:\n{out_path}")


if __name__ == '__main__':
    base     = os.path.dirname(os.path.abspath(__file__))
    md_file  = os.path.join(base, 'FINAL_PROJECT_DOCUMENTATION_UPDATED.md')
    out_file = os.path.join(base, 'FINAL_PROJECT_DOCUMENTATION_UPDATED.docx')
    build_document(md_file, out_file)
