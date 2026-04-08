USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_MakeDutyMarkAndUnmarkDuty]    Script Date: 2/10/2021 11:32:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_MakeDutyMarkAndUnmarkDuty]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE dbo.usp_MakeDutyMarkAndUnmarkDuty 
	-- Add the parameters for the stored procedure here
	@dutyId int,
	@requestStatus int,
	@isEditedStatus int,
	@fontColor varchar(12),
	@CurrentuserID int,
	@CurrentuserName varchar(100),
	@status	 INT OUTPUT,
	@returnstring VARCHAR(1000) OUTPUT
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	DECLARE @History VARCHAR(500) ='''';
	IF(@isEditedStatus =0)
	BEGIN
	 BEGIN TRANSACTION
        BEGIN TRY
    -- UPDATE statements for procedure here
	UPDATE Allocations SET BackColour = @fontColor,isRequest = @requestStatus WHERE ID = @dutyId;

	EXEC [usp_mod_AllocationHistory] @dutyId,8,@CurrentuserID,@History,1;
	COMMIT TRANSACTION
	SET @returnstring = ''Duty marked for request sucessfully!'';
	SET @status = 1;
	SELECT @status strstatus, @returnstring strsmsg;
	return
		END TRY
		BEGIN CATCH
	RoLLBACK TRANSACTION
	SET @returnstring = ''Opps! Some Error Found.'';
	SET @status = 0;
	SELECT @status strstatus, @returnstring strsmsg;
	return
		END CATCH
	END
	ELSE
	BEGIN
	 BEGIN TRANSACTION
        BEGIN TRY
    -- UPDATE statements for procedure here
		UPDATE Allocations_edit SET BackColour = @fontColor,isRequest = @requestStatus WHERE ID = @dutyId;

		EXEC [usp_mod_AllocationHistory] @dutyId,8,@CurrentuserID,@History,1;
				COMMIT TRANSACTION
				SET @returnstring = ''Duty marked for request sucessfully!'';
				SET @status = 1;
				SELECT @status strstatus, @returnstring strsmsg;
				return
			END TRY
			BEGIN CATCH
				RoLLBACK TRANSACTION
				SET @returnstring = ''Opps! Some Error Found.'';
				SET @status = 0;
				SELECT @status strstatus, @returnstring strsmsg;
				return
		END CATCH
	END
	
END
'
EXEC dbo.sp_executesql @strSQL

GO
