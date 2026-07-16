import {
  CreateUploadIntentCommand,
  CreateUploadIntentHandler,
} from './CreateUploadIntent';
import { UploadIntent } from '../../domain/UploadIntent';
import type { UploadIntentRepository } from '../../domain/UploadIntentRepository';
import type { VariantFormatCatalog } from '../../domain/VariantFormatCatalog';
import { UnknownVariantFormatError } from '../../domain/MediaErrors';

describe('CreateUploadIntentHandler', () => {
  let repository: jest.Mocked<UploadIntentRepository>;
  let variantFormats: jest.Mocked<VariantFormatCatalog>;
  let handler: CreateUploadIntentHandler;

  const command: CreateUploadIntentCommand = {
    entityGid: 'gid://shop/Product/123',
    routingKey: 'product.image',
    userId: '3fa85f64-5717-4562-b3fc-2c963f66afa6',
    variants: ['thumbnail'],
    ttlSeconds: 60,
  };

  beforeEach(() => {
    repository = {
      create: jest.fn().mockResolvedValue(undefined),
      findByToken: jest.fn().mockResolvedValue(null),
      markConsumed: jest.fn().mockResolvedValue(true),
    };
    variantFormats = {
      resolve: jest.fn().mockReturnValue({
        width: 150,
        height: 150,
        format: 'webp',
      }),
    };
    handler = new CreateUploadIntentHandler(repository, variantFormats);
  });

  it('creates an UploadIntent from the command', async () => {
    const result = await handler.execute(command);

    expect(result).toBeInstanceOf(UploadIntent);
    expect(result.entityGid).toBe(command.entityGid);
    expect(result.userId).toBe(command.userId);
    expect(result.routingKey).toBe(command.routingKey);
    expect(result.variants).toEqual(command.variants);
    expect(() => result.assertConsumable(command.userId)).not.toThrow();
  });

  it('sets expiresAt ttlSeconds in the future', async () => {
    jest.useFakeTimers().setSystemTime(new Date('2026-01-01T00:00:00.000Z'));

    const result = await handler.execute(command);

    expect(result.expiresAt).toEqual(new Date('2026-01-01T00:01:00.000Z'));

    jest.useRealTimers();
  });

  it('persists the created UploadIntent', async () => {
    const result = await handler.execute(command);

    /* eslint-disable @typescript-eslint/unbound-method */
    expect(repository.create).toHaveBeenCalledTimes(1);
    expect(repository.create).toHaveBeenCalledWith(result);
    /* eslint-enable @typescript-eslint/unbound-method */
  });

  it('rejects a variant name unknown to the catalog', async () => {
    variantFormats.resolve.mockImplementation((name: string) => {
      throw new UnknownVariantFormatError(name);
    });

    await expect(handler.execute(command)).rejects.toThrow(
      UnknownVariantFormatError,
    );
    /* eslint-disable @typescript-eslint/unbound-method */
    expect(repository.create).not.toHaveBeenCalled();
    /* eslint-enable @typescript-eslint/unbound-method */
  });
});
