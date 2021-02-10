import { schema } from 'normalizr';

const user = new schema.Entity('users', {}, {
    mergeStrategy: (entityA, entityB) => {
        return {
            ...entityA,
            ...entityB,
            permissions: [...(entityA.permissions || []), ...(entityB.permissions || [])]
        };
    },
    processStrategy: (value, parent) => {
        return { 
            ...value, 
            permissions: [parent.id]
        };
    }
});

const permissionSchema = new schema.Entity('permissions', {
    user: user
});

export const permissionListSchema = new schema.Array(permissionSchema);
