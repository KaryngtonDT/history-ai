from io import BytesIO
from pathlib import Path

from pypdf import PdfWriter
from pypdf.generic import (
    ArrayObject,
    DecodedStreamObject,
    DictionaryObject,
    NameObject,
    NumberObject,
)


def build_sample_pdf(text: str) -> bytes:
    buffer = BytesIO()
    writer = PdfWriter()
    page = writer.add_blank_page(width=612, height=792)

    stream = DecodedStreamObject()
    stream.set_data(
        f"BT /F1 24 Tf 72 720 Td ({text}) Tj ET".encode(),
    )
    page[NameObject("/Contents")] = stream
    page[NameObject("/Resources")] = DictionaryObject(
        {
            NameObject("/Font"): DictionaryObject(
                {
                    NameObject("/F1"): DictionaryObject(
                        {
                            NameObject("/Type"): NameObject("/Font"),
                            NameObject("/Subtype"): NameObject("/Type1"),
                            NameObject("/BaseFont"): NameObject("/Helvetica"),
                        },
                    ),
                },
            ),
        },
    )
    page[NameObject("/MediaBox")] = ArrayObject(
        [NumberObject(0), NumberObject(0), NumberObject(612), NumberObject(792)],
    )

    writer.write(buffer)
    return buffer.getvalue()


def main() -> None:
    fixtures_dir = Path(__file__).resolve().parents[1] / "fixtures"
    fixtures_dir.mkdir(parents=True, exist_ok=True)
    output = fixtures_dir / "sample.pdf"
    output.write_bytes(
        build_sample_pdf("The Roman Empire was a vast civilization."),
    )
    print(f"Wrote {output}")


if __name__ == "__main__":
    main()
