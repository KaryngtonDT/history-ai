import {
  DEFAULT_LUMEN_API_BASE,
  DEFAULT_LUMEN_WEB_BASE,
  getStorageSettings,
  setLumenApiBase,
  setLumenWebBase,
} from "../shared/api";

const form = document.getElementById("options-form") as HTMLFormElement;
const apiBaseInput = document.getElementById("api-base") as HTMLInputElement;
const webBaseInput = document.getElementById("web-base") as HTMLInputElement;
const savedEl = document.getElementById("saved") as HTMLParagraphElement;

async function loadSettings(): Promise<void> {
  const { lumenApiBase, lumenWebBase } = await getStorageSettings();
  apiBaseInput.value = lumenApiBase || DEFAULT_LUMEN_API_BASE;
  webBaseInput.value = lumenWebBase || DEFAULT_LUMEN_WEB_BASE;
}

form.addEventListener("submit", async (event) => {
  event.preventDefault();

  const apiBase = apiBaseInput.value.trim().replace(/\/$/, "");
  const webBase = webBaseInput.value.trim().replace(/\/$/, "");
  await setLumenApiBase(apiBase || DEFAULT_LUMEN_API_BASE);
  await setLumenWebBase(webBase || DEFAULT_LUMEN_WEB_BASE);

  savedEl.style.display = "block";
  setTimeout(() => {
    savedEl.style.display = "none";
  }, 2000);
});

void loadSettings();

export {};
