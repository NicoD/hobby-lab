/*
  Warnings:

  - You are about to drop the column `constraints` on the `UploadIntent` table. All the data in the column will be lost.
  - Added the required column `maxSizeBytes` to the `UploadIntent` table without a default value. This is not possible if the table is not empty.

*/
-- AlterTable
ALTER TABLE "UploadIntent" DROP COLUMN "constraints",
ADD COLUMN     "maxSizeBytes" INTEGER NOT NULL,
ADD COLUMN     "variants" TEXT[];

-- CreateTable
CREATE TABLE "Media" (
    "id" TEXT NOT NULL,
    "originalStorageKey" TEXT NOT NULL,
    "originalMimeType" TEXT NOT NULL,
    "originalSize" INTEGER NOT NULL,
    "variants" JSONB NOT NULL,
    "createdAt" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT "Media_pkey" PRIMARY KEY ("id")
);

-- CreateIndex
CREATE UNIQUE INDEX "Media_originalStorageKey_key" ON "Media"("originalStorageKey");
