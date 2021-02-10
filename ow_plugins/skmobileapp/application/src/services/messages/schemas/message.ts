import { schema } from 'normalizr';

const conversation = new schema.Entity('conversations', {}, {
    mergeStrategy: (entityA, entityB) => {
        return {
            ...entityA,
            ...entityB,
            messages: [...(entityA.messages || []), ...(entityB.messages || [])]
        };
    },
    processStrategy: (value, parent) => {
        return { 
            ...value, 
            messages: [(parent.tempId ? parent.tempId : parent.id)]
        };
    }
});

const messageSchema = new schema.Entity('messages', {
    conversation: conversation
}, {
    idAttribute: (message) => {
        return message.tempId ? message.tempId : message.id;
    }
});

export const messageListSchema = new schema.Array(messageSchema);
