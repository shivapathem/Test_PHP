USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[Usp_AddEditActiveChargeCodeMapping]    Script Date: 03/01/2022 21:49:09 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 01-01-2022
-- Description:	SP is used to add, edit and get Active Code Charge Code Mapping details
-- =============================================
--Usp_AddEditActiveChargeCodeMapping 0, 1, 1, 2022, 22.22, 'INSERT', 201
DECLARE @strSQL NVARCHAR(max)
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[Usp_AddEditActiveChargeCodeMapping]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '
--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[Usp_AddEditActiveChargeCodeMapping] 
	-- Add the parameters for the stored procedure here
	@MappingId			INT,
	@EstablishCodeId	INT,
	@ActiveCodeId		INT,
	@Year				INT,
	@Price				DECIMAL(6,2),
	@ActionType			VARCHAR(15),
	@UserId				INT
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

DECLARE @Status TINYINT, @StatusCode VARCHAR(3), @LastId INT
SET @Status		= 1
SET @StatusCode = '''';
SET @LastId		= 0;
BEGIN TRY
	--Validation Part Start--
	IF( @ActionType = '''' )
	BEGIN
		SET @Status = 0;
		SET @StatusCode = ''022'';
	END
	IF( @UserId = '''' )
	BEGIN
		SET @Status = 0;
		SET @StatusCode = ''023'';
	END
	IF( (@ActionType != ''INSERT'') AND (@MappingId  < 1) )
	BEGIN
		SET @Status = 0;
		SET @StatusCode = ''017'';
	END
	IF( (@ActionType = ''INSERT'') AND EXISTS(SELECT 1 FROM ActivityChargeCodeMapping_Link WHERE Year = @Year AND ActiveCodeId = @ActiveCodeId AND EstablishCodeId = @EstablishCodeId) )
	BEGIN
		SET @Status = 0;
		SET @StatusCode = ''018'';
	END
	IF( (@ActionType = ''UPDATE'') AND EXISTS(SELECT 1 FROM ActivityChargeCodeMapping_Link WHERE Year = @Year AND ActiveCodeId = @ActiveCodeId AND EstablishCodeId = @EstablishCodeId AND MappingId != @MappingId) )
	BEGIN
		SET @Status = 0;
		SET @StatusCode = ''018'';
	END
	IF( (@ActionType IN(''INSERT'', ''UPDATE'') ) AND (@Price  <= 0) )
	BEGIN
		SET @Status = 0;
		SET @StatusCode = ''021'';
	END
	IF( ( @ActionType =''INSERT'' ) AND (@EstablishCodeId <= 0) )
	BEGIN
		SET @Status = 0;
		SET @StatusCode = ''024'';
	END
	IF( ( @ActionType =''INSERT'' ) AND (@ActiveCodeId <= 0 ) )
	BEGIN
		SET @Status = 0;
		SET @StatusCode = ''005'';
	END
	--Validation Part End--
	IF(@Status = 1 AND @StatusCode = '''')
	BEGIN
		IF(@ActionType = ''INSERT'')
		BEGIN
			INSERT INTO ActivityChargeCodeMapping_Link(Year, EffectiveFrom, EstablishCodeId, ActiveCodeId, Price, CreatedBy, CreatedDate, UpdateBy, UpdatedDate)
			VALUES(@Year, CAST(@Year as varchar(6))+''-04-01'', @EstablishCodeId, @ActiveCodeId, @Price, @UserId, GETDATE(), @UserId, GETDATE())
			SET @LastId = SCOPE_IDENTITY()
			IF(@@ROWCOUNT > 0)
			BEGIN
				SET @Status = 1;
				SET @StatusCode = ''001'';
			END
			ELSE
			BEGIN
				SET @Status = 0;
				SET @StatusCode = ''019'';
			END
		END 
		IF(@ActionType = ''UPDATE'')
		BEGIN
			UPDATE ActivityChargeCodeMapping_Link SET Price = @Price, UpdateBy = @UserId, UpdatedDate = GETDATE() WHERE MappingId = @MappingId
			SET @LastId = @MappingId
			IF(@@ROWCOUNT > 0)
			BEGIN
				SET @Status = 1;
				SET @StatusCode = ''001'';
			END
			ELSE
			BEGIN
				SET @Status = 0;
				SET @StatusCode = ''019'';
			END
		END
		IF(@ActionType = ''DELETE'')
		BEGIN
			IF( EXISTS(SELECT 1 FROM ActivityChargeCodeMapping_Link ACCM JOIN ChargingDutyMapping_Link CDML ON ACCM. EstablishCodeId = CDML.EstabCodeId AND ACCM. ActiveCodeId = CDML.ActivityCodeId WHERE MappingId = @MappingId) )
			BEGIN
				SET @Status = 0;
				SET @StatusCode = ''030'';
			END
			ELSE
			BEGIN
				DELETE FROM ActivityChargeCodeMapping_Link WHERE MappingId = @MappingId
				SET @LastId = @MappingId
				IF(@@ROWCOUNT > 0)
				BEGIN
					SET @Status = 1;
					SET @StatusCode = ''001'';
				END
				ELSE
				BEGIN
					SET @Status = 0;
					SET @StatusCode = ''009'';
				END
			END
		END
	END
END TRY
BEGIN CATCH
	INSERT INTO ErrorLog
			VALUES
			(
			ERROR_NUMBER(),
			ERROR_STATE(),
			ERROR_SEVERITY(),
			ERROR_LINE(),
			''AddEditActiveChargeCodeMapping'',
			ERROR_MESSAGE(),
			GETDATE(),
			@UserId
			)
END CATCH

SELECT @Status Status, @StatusCode  StatusCode, @LastId LastId

END
'

EXEC dbo.sp_executesql @strSQL
GO