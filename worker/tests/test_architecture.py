from __future__ import annotations

import ast
import re
from pathlib import Path

APP_ROOT = Path(__file__).resolve().parents[1] / "app"
TESTS_ROOT = Path(__file__).resolve().parents[1] / "tests"
GENERATORS_ROOT = APP_ROOT / "generators"
AI_ROOT = APP_ROOT / "ai"

FORBIDDEN_GENERATOR_AI_IMPORTS = {
    "app.ai.GeminiProvider",
    "app.ai.MockAIProvider",
}

GEMINI_PROVIDER_ALLOWED_PREFIXES = (
    "app/ai/",
    "tests/",
)


def _collect_python_files(root: Path) -> list[Path]:
    return sorted(path for path in root.rglob("*.py") if path.is_file())


def _extract_imports(file_path: Path) -> list[str]:
    source = file_path.read_text(encoding="utf-8")
    tree = ast.parse(source, filename=str(file_path))
    imports: list[str] = []

    for node in ast.walk(tree):
        if isinstance(node, ast.ImportFrom) and node.module is not None:
            imports.append(node.module)
        elif isinstance(node, ast.Import):
            for alias in node.names:
                imports.append(alias.name)

    return imports


def _relative(path: Path) -> str:
    project_root = Path(__file__).resolve().parents[1]
    return str(path.relative_to(project_root)).replace("\\", "/")


def test_generators_do_not_import_concrete_ai_providers() -> None:
    violations: list[str] = []

    for file_path in _collect_python_files(GENERATORS_ROOT):
        for imported in _extract_imports(file_path):
            if imported in FORBIDDEN_GENERATOR_AI_IMPORTS:
                violations.append(
                    f"{_relative(file_path)} imports forbidden {imported}",
                )

    assert violations == [], "Generator AI dependency violations:\n" + "\n".join(
        violations,
    )


def test_gemini_provider_is_confined_to_ai_package_and_tests() -> None:
    violations: list[str] = []
    project_root = Path(__file__).resolve().parents[1]

    for file_path in _collect_python_files(project_root):
        relative = _relative(file_path)
        if relative.startswith("tests/"):
            continue

        source = file_path.read_text(encoding="utf-8")
        if "GeminiProvider" not in source:
            continue

        if not any(
            relative.startswith(prefix)
            for prefix in GEMINI_PROVIDER_ALLOWED_PREFIXES
        ):
            violations.append(
                f"{relative} references GeminiProvider outside app/ai",
            )

    assert violations == [], "GeminiProvider confinement violations:\n" + "\n".join(
        violations,
    )


def test_generators_depend_on_ai_abstractions_only() -> None:
    violations: list[str] = []
    allowed_ai_import_pattern = re.compile(
        r"^app\.ai\.(AIProviderInterface|AIProviderFactory)$",
    )

    for file_path in _collect_python_files(GENERATORS_ROOT):
        for imported in _extract_imports(file_path):
            if not imported.startswith("app.ai."):
                continue

            if allowed_ai_import_pattern.match(imported):
                continue

            violations.append(
                f"{_relative(file_path)} imports non-abstraction {imported}",
            )

    assert violations == [], "Generator abstraction violations:\n" + "\n".join(
        violations,
    )
