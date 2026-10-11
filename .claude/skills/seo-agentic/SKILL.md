---
name: seo-agentic
description: "Audit a site's readiness for AI crawlers and browsing agents: robots access, llms.txt, machine-readable content, structured data, and agent-friendly page patterns."
---

# OpenSEO Agentic Readiness

## Goal

Work out whether AI crawlers (training and retrieval bots) and AI browsing agents (ChatGPT Atlas, Claude, Perplexity, Gemini, and similar) can actually reach, parse, and act on a site's content, and what to fix first. This is a distinct question from classic crawlability: a page can be fully indexable by Googlebot and still be unreadable or unusable to an LLM-driven crawler or agent.

Use this when asked about AI visibility infrastructure, agent/bot access, llms.txt, or "can ChatGPT/Claude read my site". For AI Overviews / AI Mode citability and passage-level content quality, that is a ranking and content question, not an access question — hand off to `seo-audit` or a content-quality pass instead.

## Required inputs

- Domain or URL to audit
- `projectId` (use `list_projects`; if no project matches the domain, create one with `create_project`)

## Project context

1. Call `get_project_context` first. What the business does and sells decides which agent interactions matter (a SaaS with a pricing API matters for agentic commerce; a blog mostly needs to not be blocked).
2. Before spending credits on anything paid below, check the research log for a run on this domain in the last 30 days; reuse and say so instead of re-buying it.
3. On finish, append a research log entry: `{ appendResearchLog: { summary: "Agentic readiness: <domain>. Verdict: <conclusion>" } }`.

## What to check

Fetch each of these directly (robots.txt, llms.txt, and a representative page's raw HTML and rendered DOM); do not infer from memory or assume a file exists.

1. **robots.txt bot directives.** Look for explicit `Disallow` rules against named AI user-agents: `GPTBot`, `ChatGPT-User`, `OAI-SearchBot`, `ClaudeBot`, `Claude-User`, `anthropic-ai`, `Google-Extended`, `PerplexityBot`, `Perplexity-User`, `CCBot`, `Bytespider`, `Applebot-Extended`, `Amazonbot`, `Meta-ExternalAgent`. Distinguish three categories and report them separately: bots blocked entirely, bots allowed to crawl but blocked from specific paths, and bots with no rule (inherit the wildcard `*` block if one exists). A blanket `Disallow: /` under `User-agent: *` with no AI-specific carve-out silently blocks every agent and is worth flagging as the single biggest issue when present.
2. **llms.txt**, at `/llms.txt`. If present, check it is actually Markdown with working links (not a copy-pasted sitemap or a stub), lists real pages that return 200, and is kept short enough to be useful (a few hundred lines, not a full site dump). State plainly in the report that llms.txt is not a current Google ranking or AI Overview citation input and has no confirmed adoption by major AI crawlers as of this writing; frame it as a nice-to-have content index for agents that do choose to read it, never as a visibility lever. Do not let its absence inflate the overall score — weight it lightly.
3. **Content accessibility without JavaScript.** Fetch the page's raw HTML (no rendering) and check whether the primary content (what a human visiting the page would read) is present in that raw response, versus requiring client-side rendering to appear. Many AI crawlers do not execute JavaScript; a page that is an empty `<div id="root">` shell in raw HTML is invisible to them even if Googlebot renders it fine. This is the check most likely to be missed by a generic technical audit.
4. **Structured data for machine parsing.** JSON-LD presence and validity (reuse `seo-schema` territory, but here the lens is "can an agent extract facts" not "does this earn a rich result"): Organization/Product/FAQPage-equivalent facts, pricing, availability. Flag pages where the only source of a key fact (price, hours, availability) is a rendered visual element with no structured or plain-text equivalent.
5. **Agent-friendly interaction patterns**, per web.dev's agent-friendly-page guidance: forms with labeled, programmatically-associated inputs rather than placeholder-only labels; primary actions reachable as real links/buttons rather than only via JS click handlers with no href or accessible name; no mandatory CAPTCHA or bot-interstitial in front of the content the user asked about; paginated or infinite-scroll content that has a crawlable fallback (numbered page URLs, or a "load more" that is a real link).
6. **WebMCP / tool manifest.** Check for a WebMCP manifest or an equivalent declared tool surface (e.g. `/.well-known/mcp.json` or linked from the page). Most sites will not have one; absence is normal, not a deduction, unless the project context says the business specifically wants agentic-commerce readiness (e.g. a storefront wanting AI shopping agents to transact).

## Workflow

1. Resolve the `projectId`, then fetch `robots.txt` for the domain directly.
2. Fetch `llms.txt` directly; note 200 vs. 404 and, if present, skim it for the staleness/dump problems above.
3. Pick the 1-2 pages that matter most for this business (homepage plus one money page from `get_project_context`'s key pages, or ask which page if none are recorded). Fetch each one twice: once for raw HTML, once rendered (if a rendering fetch is available in this environment) or visually. Diff what content is present in each.
4. Check structured data on those same pages: presence, type, and whether it duplicates facts that are otherwise only in rendered-only content.
5. Scan the rendered page for the interaction-pattern issues in point 5 above, focused on the page's primary action (buy, book, contact, sign up).
6. Check for a WebMCP manifest only if the project context or the user indicates agentic-commerce matters.
7. Score the site's agent readiness as a short, evidence-backed verdict (not a fabricated numeric sub-score out of some invented maximum) organized as: blocking issues (an AI bot or agent cannot get the content at all), degrading issues (it gets partial or wrong content), and non-issues correctly already handled. Order fixes by how many bots/agents each one unblocks.
8. Deliver a short report: what's blocked and for which bots, what's JS-gated, what structured data is missing where it would help, and the 2-3 fixes to make first. Include the exact robots.txt line(s) to add or remove.

## Guardrails

- Never claim llms.txt affects rankings, AI Overview inclusion, or citation frequency. If the user asks you to prioritize it over access or rendering fixes, say plainly that the access and rendering issues matter more and why.
- Don't recommend blocking AI training crawlers as a default; that's a business policy decision (content licensing, competitive concerns), not an SEO best practice. Present the tradeoff and let the user decide, except for findings you were asked to fix.
- A bot with no explicit rule and no wildcard block is allowed; don't report that as blocked.
- Verify every claim against something you actually fetched in this run. A robots.txt or llms.txt check from a cached memory of "sites like this usually..." is not evidence.
- Keep the tone calm and plain, consistent with other OpenSEO reports: no severity inflation, gloss jargon (user-agent, JSON-LD, rendered DOM) on first use.
