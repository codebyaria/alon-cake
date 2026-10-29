export function FieldError({ id, message }: { id?: string; message?: string }) {
    if (!message) return null;

    return (
        <p id={id} role="alert" className="text-[13px] text-[#9B3B2E]">
            {message}
        </p>
    );
}
