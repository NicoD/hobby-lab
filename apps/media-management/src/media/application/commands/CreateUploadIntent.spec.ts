import {
  CreateUploadIntentCommand,
  CreateUploadIntentHandler,
} from './CreateUploadIntent';
import { UploadIntent } from '../../domain/UploadIntent';
import type { UploadIntentRepository } from '../../domain/UploadIntentRepository';

describe('CreateUploadIntentHandler', () => {
  let repository: jest.Mocked<UploadIntentRepository>;
  let handler: CreateUploadIntentHandler;

  const command: CreateUploadIntentCommand = {
    entityGid: 'gid://shop/Product/123',
    routingKey: 'product.image',
    userId: '3fa85f64-5717-4562-b3fc-2c963f66afa6',
    constraints: {
      formats: ['jpeg'],
      maxSizeBytes: 1024,
      variants: [{ name: 'thumbnail', format: 'webp' }],
    },
    ttlSeconds: 60,
  };

  beforeEach(() => {
    repository = { save: jest.fn().mockResolvedValue(undefined) };
    handler = new CreateUploadIntentHandler(repository);
  });

  it('creates an UploadIntent from the command', async () => {
    const result = await handler.execute(command);

    expect(result).toBeInstanceOf(UploadIntent);
    expect(result.entityGid).toBe(command.entityGid);
    expect(result.userId).toBe(command.userId);
    expect(result.routingKey).toBe(command.routingKey);
    expect(result.constraints).toEqual(command.constraints);
    expect(result.consumedAt).toBeNull();
  });

  it('sets expiresAt ttlSeconds in the future', async () => {
    jest.useFakeTimers().setSystemTime(new Date('2026-01-01T00:00:00.000Z'));

    const result = await handler.execute(command);

    expect(result.expiresAt).toEqual(new Date('2026-01-01T00:01:00.000Z'));

    jest.useRealTimers();
  });

  it('persists the created UploadIntent', async () => {
    const result = await handler.execute(command);

    expect(repository.save).toHaveBeenCalledTimes(1);
    expect(repository.save).toHaveBeenCalledWith(result);
  });
});
