---
name: seo-content-brief
description: "Turn a target keyword into a SERP-informed content brief a writer can work from: intent, competitive coverage, structure, internal links, and a quality gate."
---

# OpenSEO Content Brief

## Goal

Produce the handoff document between "this keyword is worth targeting" and "someone writes the page": target intent, what the current top results actually cover (so the draft can match or beat it), a heading structure, the questions it must answer, internal links to add, and a checklist to grade the finished draft against. The brief exists so a writer never has to reverse-engineer the SERP themselves, and so a reviewer has something concrete to check a draft against besides taste.

Use this after `keyword-research` or `keyword-clustering` has identified a keyword worth a dedicated page, and before anyone starts writing. For analyzing one existing page, use `seo-audit` or `seo-content` territory instead; this skill is for a page that doesn't exist yet (or is being substantially rewritten).

## Required inputs

- Target keyword (one primary keyword per brief; a cluster with multiple intents needs multiple briefs)
- `projectId`
- The page this brief is for: new page, or an existing URL being rewritten (changes a step below)

## Project context

1. Call `get_project_context` first. The brief's tone, audience, and which competitors matter all come from `business_overview` and the existing `competitors` list — a brief written without this just restates the SERP.
2. Check `list_saved_keywords` for this keyword before spending credits on fresh research; reuse saved volume/difficulty data if present.
3. On finish, append a research log entry: `{ appendResearchLog: { summary: "Content brief: <keyword>. Page type: <type>." } }`. Briefs don't belong in the durable project fields themselves (they're per-page artifacts, not site-level facts).

## OpenSEO MCP tools

- `get_serp_results`: the actual ranking page for the keyword right now. This is the brief's foundation — read it for organic results, featured snippet, People Also Ask, and any other SERP features present.
- `research_keywords` / `get_keyword_metrics`: volume and difficulty for the primary keyword and for the related/secondary terms that should become subheadings.
- `find_serp_competitors`: confirms which domains actually compete for this term, beyond the project's named competitors.
- `get_domain_keyword_suggestions`: additional angles the target domain could realistically also rank for with the same page.

Spend modestly: one SERP pull, one keyword-metrics call for the primary term plus up to 5 related terms, one competitor check. Skip `get_domain_keyword_suggestions` if the keyword is already well-scoped.

## Workflow

1. Resolve `projectId` and confirm the keyword isn't already targeted by an existing page on the site (check `get_project_context`'s key pages; a brief for a keyword that already has a ranking page risks cannibalization — flag it and ask before continuing).
2. `get_serp_results` for the keyword. Identify: the dominant page type among the top 5-10 results (listicle, single definitive guide, comparison, product/service page, tool), any featured snippet (note its exact format: paragraph, list, table), and every People Also Ask question shown.
3. Fetch the raw content of the top 3-5 ranking pages directly (not just metadata) to see actual structure: their H1/H2 pattern, approximate word count, what sections recur across all of them (the "table stakes" a new page can't skip), and anything only one of them covers (a differentiation opening).
4. Classify search intent from the SERP evidence (informational / commercial investigation / transactional / navigational) and from the dominant page type — don't assume intent from the keyword's wording alone; the SERP overrides a guess every time.
5. Pull keyword metrics for the primary term and 3-6 closely related terms found in the ranking pages' headings or PAA; these become the brief's subtopic/heading candidates, not a keyword-stuffing list.
6. Decide the target depth: word count as a range derived from the actual top-ranking pages' lengths (state the range and which pages it's based on), not a generic "1500-2000 words" default.
7. Identify 1-3 internal pages from the project's key pages that this new page should link to (and, where sensible, that should link back to it), and note the anchor text angle.
8. Note schema recommendations in one line (defer the actual markup to `seo-schema`); flag whether this page type typically carries FAQPage-equivalent, Article, Product, or other structured data based on what the ranking pages use.
9. Write the brief (see Output format). Keep it a brief, not a draft: no finished prose, only the spec a writer executes against.

## Output format

Deliver as a single markdown document (or HTML if the user's workflow prefers a shareable report):

- **Target**: primary keyword, search volume, difficulty, intent classification, and the one-sentence reasoning for that intent.
- **Page type**: what to build (guide, comparison, product page, tool, etc.) and why, citing what dominates the SERP.
- **SERP snapshot**: current top 5 URLs with one-line notes on each, the featured snippet format if one exists, and the full PAA question list.
- **Must-answer questions**: every question the page needs to address to compete, pulled from PAA and from what every top result covers.
- **Suggested structure**: an H1 and ordered H2/H3 outline, each heading tied to a question or subtopic it resolves. Mark which headings are table-stakes (every competitor has them) versus differentiation (none of them do).
- **Target depth**: word-count range with its source (the pages it was derived from).
- **Internal links**: pages to link to/from, with the anchor-text angle.
- **Evidence requirements**: what claims need a citation, data point, or first-hand example to be credible for this topic (tie to E-E-A-T, especially for YMYL-adjacent topics).
- **Schema note**: one line on expected structured data type.
- **Quality gate**: a short checklist the finished draft must pass (answers every must-answer question; hits the structural table-stakes; includes the internal links; matches or exceeds competitor depth on the differentiating sections) — this is what a reviewer checks the draft against, not a new round of opinions.

## Guardrails

- Never invent SERP data, PAA questions, or competitor word counts; everything in the brief must trace back to something actually fetched in this run.
- Word-count targets are a range grounded in real competitor pages, never an arbitrary round number.
- Keep the brief to spec, not prose: no sample paragraphs, no written introduction for the page. If asked to also draft the page, that's a separate step after the brief is approved.
- Flag keyword cannibalization risk before writing a brief for a term the site already targets; don't silently proceed.
- A thin SERP (few real competitors, weak existing content) is itself a finding worth stating plainly — it changes the opportunity assessment, not just the brief's content.
