USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[Usp_ActivityOperations]    Script Date: 23/12/2021 20:32:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
DECLARE @strSQL NVARCHAR(max)
-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	<Description,,>
-- =============================================
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[Usp_ActivityOperations]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '
--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[Usp_ActivityOperations] 
	-- Add the parameters for the stored procedure here
	@ActivityCodeId		INT,
	@ActivityCodeName	VARCHAR(10),
	@Description		VARCHAR(50),
	@ActionType			VARCHAR(15),
	@UserId				INT,
	@IsActive			TINYINT,
	@HistoryStr			VARCHAR(4000)
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
	IF( (@HistoryStr = '''') AND (@ActionType != ''SELECT'') )
	BEGIN
		SET @Status = 0;
		SET @StatusCode = ''004'';
	END
	IF( @ActivityCodeId = '''' AND @ActionType IN(''UPDATE'', ''UPDATESTATUS'') )
	BEGIN
		SET @Status = 0;
		SET @StatusCode = ''005'';
	END
	IF( (@ActionType = ''INSERT'') AND (@ActivityCodeName  = '''') )
	BEGIN
		SET @Status = 0;
		SET @StatusCode = ''006'';
	END
	IF( (@ActionType IN(''INSERT'', ''UPDATE'')) AND (@Description  = '''') )
	BEGIN
		SET @Status = 0;
		SET @StatusCode = ''007'';
	END
	IF( (@ActionType = ''UPDATESTATUS'') AND (@Status  NOT IN(0, 1)) )
	BEGIN
		SET @Status = 0;
		SET @StatusCode = ''008'';
	END
	IF( (@ActionType = ''INSERT'') AND EXISTS(SELECT 1 FROM ACTIVITYCODE WHERE ActivityCodeName = @ActivityCodeName) )
	BEGIN
		SET @Status = 0;
		SET @StatusCode = ''015'';
	END
	IF( (@ActionType = ''UPDATE'') AND EXISTS(SELECT 1 FROM ACTIVITYCODE WHERE ActivityCodeName = @ActivityCodeName AND ActivityCodeId != @ActivityCodeId) )
	BEGIN
		SET @Status = 0;
		SET @StatusCode = ''015'';
	END
	--Validation Part End--
	IF(@Status = 1 AND @StatusCode = '''')
	BEGIN
		IF(@ActionType = ''SELECT'')
		BEGIN
			SELECT * FROM ACTIVITYCODE ORDER BY IsActive DESC, ActivityCodeName ASC, Description ASC;
		END
		IF(@ActionType = ''INSERT'')
		BEGIN
			INSERT INTO activitycode(ActivityCodeName, Description, IsActive, CreatedBy, CreatedDate, ModifiedBy, ModifiedDate, History) VALUES(@ActivityCodeName, @Description, 1, @UserId, GETDATE(), @UserId, GETDATE(), @HistoryStr)
			SET @LastId = SCOPE_IDENTITY()
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
		IF(@ActionType = ''UPDATE'')
		BEGIN
			UPDATE activitycode SET Description = @Description, ModifiedBy = @UserId, ModifiedDate = GETDATE(), History = CONCAT(History, @HistoryStr) WHERE ActivityCodeId = @ActivityCodeId
			SET @LastId = @ActivityCodeId
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
		IF(@ActionType = ''UPDATESTATUS'')
		BEGIN
			UPDATE activitycode SET IsActive = @IsActive, ModifiedBy = @UserId, ModifiedDate = GETDATE(), History = CONCAT(History, @HistoryStr) WHERE ActivityCodeId = @ActivityCodeId
			SET @LastId = @ActivityCodeId
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
END TRY
BEGIN CATCH
	INSERT INTO ErrorLog
			VALUES
			(
			ERROR_NUMBER(),
			ERROR_STATE(),
			ERROR_SEVERITY(),
			ERROR_LINE(),
			''Usp_ActivityOperations'',
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