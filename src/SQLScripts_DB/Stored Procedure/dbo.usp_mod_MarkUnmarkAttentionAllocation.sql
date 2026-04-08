USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_MarkUnmarkAttentionAllocation]    Script Date: 17/11/2021 13:32:16 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_mod_MarkUnmarkAttentionAllocation]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N'PROCEDURE [dbo].[usp_mod_MarkUnmarkAttentionAllocation] 
	-- Add the parameters for the stored procedure here
	@allocationId INT,
	@isAttention INT,
	@userId INT,
	@username VARCHAR(100),
	@status	 INT OUTPUT,
	@returnstring VARCHAR(1000) OUTPUT
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	DECLARE @History VARCHAR(500) ='''',
	@text char(20);
	
	 BEGIN TRANSACTION
        BEGIN TRY
    -- UPDATE statements for procedure here
	UPDATE Allocations SET isAttention = @isAttention WHERE ID = @allocationId;
	if(@isAttention=1)
	BEGIN
	SET @History =''Marked for Attention by ''+ @username + '' on ''+ CAST(GETDATE() AS varchar);
	SET @text=''Marked for Attention'';
	END
	ELSE
	BEGIN
	
	SET @History =''Unmarked for Attention by ''+ @username + '' on ''+ CAST(GETDATE() AS varchar);
	SET @text=''Unmarked for Attention'';
	END

	EXEC [usp_mod_AllocationHistory] @allocationId,8,@userId,@History,1;
	COMMIT TRANSACTION
	SET @returnstring = ''Duty ''+@text+'' sucessfully!'';
	SET @status = 1;
	SELECT @status strstatus, @returnstring strsmsg;
	return
		END TRY
		BEGIN CATCH
	RoLLBACK TRANSACTION
	SET @returnstring = ''Oops! Some Error Found.'';
	SET @status = 0;
	SELECT @status strstatus, @returnstring strsmsg;
	return
	END CATCH
END
'

EXEC dbo.sp_executesql @strSQL

GO
