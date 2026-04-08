USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_SetActiveChargeWbsCode]    Script Date: 2/10/2021 11:32:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_mod_SetActiveChargeWbsCode]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE dbo.usp_mod_SetActiveChargeWbsCode
	-- Add the parameters for the stored procedure here
	@chargewbscodeid int,
@userid int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	DECLARE @returncode varchar(100),@status bit,@codetype int,@isactive bit

	SET @returncode = ''001''
	SET @status = 1
	SELECT @codetype = CodeType,@isactive = IsActive from ChargeWbsCode where ChargeWbsCodeId = @chargewbscodeid

	IF @isactive = 0
		BEGIN
			SET @isactive = 1
		END
	ELSE
		BEGIN
			SET @isactive = 0
		END
		--Update the active/deactive code record
	BEGIN TRANSACTION
            BEGIN TRY
				Update ChargeWbsCode
					SET isActive = @isactive,ModifiedDate = GETDATE(),ModifiedBy = @userid
					Where ChargeWbsCodeId = @chargewbscodeid

	COMMIT TRANSACTION
			END TRY
			 BEGIN CATCH
						ROLLBACK TRANSACTION
            			INSERT INTO ErrorLog
						VALUES
					  (
					   ERROR_NUMBER(),
					   ERROR_STATE(),
					   ERROR_SEVERITY(),
					   ERROR_LINE(),
					   ''usp_mod_SetActiveChargeWbsCode'',
					   ERROR_MESSAGE(),
					   GETDATE(),
					   @userid
					  );
					  SET @status = 0
					IF @codetype = 1
					BEGIN
						SET @returncode = ''002''
					END
				ELSE
					BEGIN
						SET @returncode = ''003''
					END

			END CATCH


SELECT @returncode StrStatus,@status Status;


END
'
EXEC dbo.sp_executesql @strSQL

GO
