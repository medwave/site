#!/usr/bin/env node
// Fetches Microsoft Clarity's Project Live Insights export data.
// Docs: https://learn.microsoft.com/en-us/clarity/data-export/data-export-api
//
// Usage:
//   CLARITY_API_TOKEN=xxx node scripts/clarity-live-insights.mjs [--days 1] [--dimension Browser] [--dimension Country]
//
// Or set CLARITY_API_TOKEN in .env (copy .env.example) and just run:
//   node scripts/clarity-live-insights.mjs
//
// Clarity enforces a rate limit of 10 requests per project per day.

const ENDPOINT = "https://www.clarity.ms/export-data/api/v1/project-live-insights";

try {
  process.loadEnvFile();
} catch {
  // .env is optional; env vars may already be set in the shell.
}

function parseArgs(argv) {
  const args = { days: null, dimensions: [] };
  for (let i = 0; i < argv.length; i++) {
    const arg = argv[i];
    if (arg === "--days") {
      args.days = argv[++i];
    } else if (arg === "--dimension") {
      args.dimensions.push(argv[++i]);
    } else {
      throw new Error(`Unknown argument: ${arg}`);
    }
  }
  return args;
}

async function fetchLiveInsights({ token, days, dimensions }) {
  const url = new URL(ENDPOINT);
  if (days != null) url.searchParams.set("numOfDays", days);
  dimensions.forEach((dim, i) => url.searchParams.set(`dimension${i + 1}`, dim));

  const response = await fetch(url, {
    headers: { Authorization: `Bearer ${token}` },
  });

  const body = await response.text();
  if (!response.ok) {
    throw new Error(`Clarity API request failed (${response.status}): ${body}`);
  }
  return JSON.parse(body);
}

async function main() {
  const token = process.env.CLARITY_API_TOKEN;
  if (!token) {
    console.error("Missing CLARITY_API_TOKEN. Set it in .env or the environment.");
    process.exit(1);
  }

  const { days, dimensions } = parseArgs(process.argv.slice(2));
  if (dimensions.length > 3) {
    console.error("Clarity supports at most 3 dimensions per request.");
    process.exit(1);
  }

  const data = await fetchLiveInsights({ token, days, dimensions });
  console.log(JSON.stringify(data, null, 2));
}

main().catch((err) => {
  console.error(err.message);
  process.exit(1);
});
