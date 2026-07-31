import { describe, it, expect } from 'vitest';
import state from '../editor/state.js';

describe('state', () => {
    it('should have default values', () => {
        expect(state.pageId).toBeNull();
        expect(state.selectedId).toBeNull();
        expect(state.activeTab).toBe('content');
        expect(state.responsiveMode).toBe('desktop');
        expect(state.zoomLevel).toBe(100);
        expect(state.isFullscreen).toBe(false);
        expect(state.saving).toBe(false);
        expect(state.dirty).toBe(false);
    });

    it('should allow setting values', () => {
        state.pageId = 1;
        state.selectedId = 42;
        expect(state.pageId).toBe(1);
        expect(state.selectedId).toBe(42);
    });
});
