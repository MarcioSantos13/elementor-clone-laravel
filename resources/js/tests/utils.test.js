import { describe, it, expect } from 'vitest';
import { escHtml, structureIcon } from '../editor/utils.js';

describe('escHtml', () => {
    it('should escape HTML special characters', () => {
        expect(escHtml('<script>alert("xss")</script>')).toBe('&lt;script&gt;alert("xss")&lt;/script&gt;');
    });

    it('should return empty string for empty input', () => {
        expect(escHtml('')).toBe('');
    });

    it('should keep plain text unchanged', () => {
        expect(escHtml('hello world')).toBe('hello world');
    });
});

describe('structureIcon', () => {
    it('should return known icons', () => {
        expect(structureIcon('section')).toBe('&#9638;');
        expect(structureIcon('heading')).toBe('H');
        expect(structureIcon('text')).toBe('T');
        expect(structureIcon('image')).toBe('&#128247;');
        expect(structureIcon('button')).toBe('&#128206;');
    });

    it('should return default icon for unknown type', () => {
        expect(structureIcon('unknown')).toBe('&#9679;');
    });
});
