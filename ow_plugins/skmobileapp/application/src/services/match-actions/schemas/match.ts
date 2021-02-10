import { schema } from 'normalizr';

const user = new schema.Entity('user', {}, {
    processStrategy: (value, parent) => {
        return { 
            ...value,
            matchAction: parent.id
        };
    }
});

export const matchSchema = new schema.Entity('matchAction', {
    user: user
});
