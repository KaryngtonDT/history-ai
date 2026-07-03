import {
  DEFAULT_LUMEN_API_BASE,
  getStorageSettings,
  setLumenApiBase,
} from "../shared/api";

const form = document.getElementById("options-form") as HTMLFormElement;
const apiBaseInput = document.getElementById("api-base") as HTMLInputElement;
const savedEl = document.getElementById("saved") as HTMLParagraphElement;

async function loadSettings(): Promise<void> {
  const { lumenApiBase } = await getStorageSettings();
  apiBaseInput.value = lumenApiBase || DEFAULT_LUMEN_API_BASE;
}

form.addEventListener("submit", async (event) => {
  event.preventDefault();

  const value = apiBaseInput.value.trim().replace(/\/$/, "");
  await setLumenApiBase(value || DEFAULT_LUMEN_API_BASE);

  savedEl.style.display = "block";
  setTimeout(() => {
    savedEl.style.display = "none";
  }, 2000);
});

void loadSettings();

export {};
